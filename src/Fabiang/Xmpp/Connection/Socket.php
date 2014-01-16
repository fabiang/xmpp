<?php

/**
 * Copyright 2014 Fabian Grutschus. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * The views and conclusions contained in the software and documentation are those
 * of the authors and should not be interpreted as representing official policies,
 * either expressed or implied, of the copyright holders.
 *
 * @author    Fabian Grutschus <f.grutschus@lubyte.de>
 * @copyright 2014 Fabian Grutschus. All rights reserved.
 * @license   BSD
 * @link      http://github.com/fabiang/xmpp
 */

namespace Fabiang\Xmpp\Connection;

use Psr\Log\LogLevel;
use Fabiang\Xmpp\Stream\SocketClient;
use Fabiang\Xmpp\Stream\XMLStream;
use Fabiang\Xmpp\Event\EventManager;
use Fabiang\Xmpp\Event\EventManagerInterface;
use Fabiang\Xmpp\EventListener\EventListenerInterface;
use Fabiang\Xmpp\EventListener\BlockingEventListenerInterface;
use Fabiang\Xmpp\Util\XML;

/**
 * Connection to a socket stream.
 *
 * @package Xmpp\Connection
 */
class Socket implements ConnectionInterface, SocketConnectionInterface
{

    const DEFAULT_LENGTH = 4096;
    const STREAM_START   = '<?xml version="1.0" encoding="UTF-8"?><stream:stream to="%s" xmlns:stream="http://etherx.jabber.org/streams" xmlns="jabber:client" version="1.0">';
    const STREAM_END     = '</stream:stream>';
    
    /**
     * Eventmanager.
     *
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * Output XML stream.
     *
     * @var XMLStream
     */
    protected $outputStream;

    /**
     * Input XML stream.
     *
     * @var XMLStream
     */
    protected $inputStream;

    /**
     * Socket.
     *
     * @var Socket
     */
    protected $socket;

    /**
     * Is stream connected.
     *
     * @var boolean
     */
    protected $connected = false;

    /**
     * Connection address.
     *
     * @var string
     */
    protected $address;

    /**
     * Event listeners.
     *
     * @var EventListenerInterface[]
     */
    protected $listeners = array();

    /**
     * Server hostname; is send as attribute "to".
     *
     * @var string
     */
    protected $to;

    /**
     * Is stream ready.
     *
     * @var boolean
     */
    protected $ready = false;

    /**
     * Constructor set default socket instance if no socket was given.
     *
     * @param StreamSocket $socket  Socket instance
     * @param string       $address Server address
     */
    public function __construct(SocketClient $socket, $address)
    {
        $this->setSocket($socket);
        $this->address = $address;
        $this->setTo(parse_url($address, PHP_URL_HOST));
    }

    /**
     * Factory for connection class.
     *
     * @param string $address Server address
     * @return static
     */
    public static function factory($address)
    {
        $socket = new SocketClient($address);
        return new static($socket, $address);
    }

    /**
     * {@inheritDoc}
     */
    public function receive()
    {
        $buffer = $this->getSocket()->read(static::DEFAULT_LENGTH);

        if ($buffer) {
            $this->log("Received buffer '$buffer' from '{$this->address}'", LogLevel::DEBUG);
            $this->getInputStream()->parse($buffer);
            return $buffer;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function send($buffer)
    {
        if (false === $this->isConnected()) {
            $this->connect();
        }

        $this->log("Sending data '$buffer' to '{$this->address}'", LogLevel::DEBUG);
        $this->getSocket()->write($buffer);
        $this->getOutputStream()->parse($buffer);

        while ($this->checkBlockingListeners()) {
            $this->receive();
        }
    }

    /**
     * Check blocking event listeners.
     *
     * @return boolean
     */
    protected function checkBlockingListeners()
    {
        $blocking = false;
        foreach ($this->listeners as $listerner) {
            $instanceof = $listerner instanceof BlockingEventListenerInterface;
            if ($instanceof && true === $listerner->isBlocking()) {
                $this->log('Listener "' . get_class($listerner) . '" is currently blocking', LogLevel::DEBUG);
                $blocking = true;
            }
        }

        return $blocking;
    }

    /**
     * {@inheritDoc}
     */
    public function connect()
    {
        if (false === $this->connected) {
            $this->getSocket()->connect();
            $this->getSocket()->setBlocking(true);
            $this->connected = true;
            $this->log("Connected to '{$this->address}'", LogLevel::DEBUG);
        }

        $this->send(sprintf(static::STREAM_START, XML::quote($this->getTo())));
    }
    
    /**
     * Call logging event.
     * 
     * @param string  $message Log message
     * @param integer $level   Log level
     * @return void
     */
    protected function log($message, $level = LogLevel::DEBUG)
    {
        $this->getEventManager()->trigger('logger', $this, array($message, $level));
    }

    /**
     * {@inheritDoc}
     */
    public function resetStreams()
    {
        $this->getInputStream()->reset();
        $this->getOutputStream()->reset();
    }

    /**
     * {@inheritDoc}
     */
    public function disconnect()
    {
        if (true === $this->connected) {
            $this->send(static::STREAM_END);
            $this->getSocket()->close();
            $this->connected = false;
            $this->log("Disconnected from '{$this->address}'", LogLevel::DEBUG);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * Return socket instance.
     *
     * @return StreamSocket
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * {@inheritDoc}
     */
    public function setSocket(SocketClient $socket)
    {
        $this->socket = $socket;
        return $this;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getEventManager()
    {
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    /**
     * {@inheritDoc}
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $this->events = $events;
        return $this;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getOutputStream()
    {
        if (null === $this->outputStream) {
            $this->outputStream = new XMLStream();
        }

        return $this->outputStream;
    }

    /**
     * {@inheritDoc}
     */
    public function getInputStream()
    {
        if (null === $this->inputStream) {
            $this->inputStream = new XMLStream();
        }

        return $this->inputStream;
    }

    /**
     * {@inheritDoc}
     */
    public function setOutputStream(XMLStream $outputStream)
    {
        $this->outputStream = $outputStream;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setInputStream(XMLStream $inputStream)
    {
        $this->inputStream = $inputStream;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addListener(EventListenerInterface $eventListener)
    {
        $this->listeners[] = $eventListener;
        return $this;
    }

    /**
     * Get registered listeners.
     *
     * @return array
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     * Get server hostname (to).
     *
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set server hostname (to).
     *
     * @param string $to Server hostname
     * @return $this
     */
    public function setTo($to)
    {
        $this->to = (string) $to;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setReady($flag)
    {
        $this->ready = (bool) $flag;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isReady()
    {
        return $this->ready;
    }

}
