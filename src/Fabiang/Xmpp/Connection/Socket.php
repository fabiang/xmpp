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

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Fabiang\Xmpp\Stream\SocketClient;
use Fabiang\Xmpp\Stream\XMLStream;
use Fabiang\Xmpp\EventListener\EventListenerInterface;
use Fabiang\Xmpp\EventListener\BlockingEventListenerInterface;

/**
 * Connection to a stream.
 *
 * @package Xmpp\Connection
 */
class Socket implements ConnectionInterface, SocketConnectionInterface
{

    const DEFAULT_LENGTH = 4096;

    /**
     *
     * @var XMLStream
     */
    protected $outputStream;

    /**
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
     * Logger instance.
     *
     * @var LoggerInterface
     */
    protected $logger;

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
     * Constructor set default socket instance if no socket was given.
     *
     * @param StreamSocket $socket  Socket instance
     * @param string       $address Server address
     */
    public function __construct(SocketClient $socket, $address)
    {
        $this->setSocket($socket);
        $this->address = $address;
    }

    /**
     *
     * @param string $address Server address
     * @return self
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
        $this->log(LogLevel::DEBUG, "Received buffer '$buffer' from '{$this->address}'");
        $this->getInputStream()->parse($buffer);
        return $buffer;
    }

    /**
     * {@inheritDoc}
     */
    public function send($buffer)
    {
        $this->log(LogLevel::DEBUG, "Sending data '$buffer' to '{$this->address}'");
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
        foreach ($this->listeners as $listerner) {
            $instanceof = $listerner instanceof BlockingEventListenerInterface;
            if ($instanceof && true === $listerner->isBlocking()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function connect()
    {
        $this->getSocket()->connect();
        $this->getSocket()->setBlocking(true);
        $this->connected = true;
        $this->log(LogLevel::DEBUG, "Connected to '{$this->address}'");
    }

    /**
     * {@inheritDoc}
     */
    public function disconnect()
    {
        if ($this->connected) {
            $this->getSocket()->close();
            $this->connected = false;
            $this->log(LogLevel::DEBUG, "Disconnected from '{$this->address}'");
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
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Mapper function for logger interface.
     *
     * See {@link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md} for more
     * information about PSR-3 logging standard.
     *
     * @param string $level   Log level
     * @param string $message Log message
     * @param array  $context Context parameters (optional)
     * @return void
     */
    protected function log($level, $message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
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

}
