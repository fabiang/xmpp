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
use Fabiang\Xmpp\Util\XML;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Exception\TimeoutException;

/**
 * Connection to a socket stream.
 *
 * @package Xmpp\Connection
 */
class Socket extends AbstractConnection implements SocketConnectionInterface
{

    const DEFAULT_LENGTH = 4096;
    const STREAM_START   = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<stream:stream to="%s" xmlns:stream="http://etherx.jabber.org/streams" xmlns="jabber:client" version="1.0">
XML;
    const STREAM_END     = '</stream:stream>';

    /**
     * Socket.
     *
     * @var SocketClient
     */
    protected $socket;

    /**
     * Did we received any data yet?
     *
     * @var bool
     */
    private $receivedAnyData = false;

    /**
     * Constructor set default socket instance if no socket was given.
     *
     * @param StreamSocket $socket  Socket instance
     */
    public function __construct(SocketClient $socket)
    {
        $this->setSocket($socket);
    }

    /**
     * Factory for connection class.
     *
     * @param Options $options Options object
     * @return static
     */
    public static function factory(Options $options)
    {
        $socket = new SocketClient($options->getAddress());
        $object = new static($socket);
        $object->setOptions($options);
        return $object;
    }

    /**
     * {@inheritDoc}
     */
    public function receive()
    {
        $buffer = $this->getSocket()->read(static::DEFAULT_LENGTH);

        if ($buffer) {
            $this->receivedAnyData = true;
            $address = $this->getAddress();
            $this->log("Received buffer '$buffer' from '{$address}'", LogLevel::DEBUG);
            $this->getInputStream()->parse($buffer);
            return $buffer;
        }

        try {
            $this->checkTimeout($buffer);
        } catch (TimeoutException $exception) {
            $this->reconnectTls($exception);
        }
    }

    /**
     * Try to reconnect via TLS.
     *
     * @param TimeoutException $exception
     * @return null
     * @throws TimeoutException
     */
    private function reconnectTls(TimeoutException $exception)
    {
        // check if we didn't receive any data
        // if not we re-try to connect via TLS
        if (false === $this->receivedAnyData) {
            $matches = array();
            $previousAddress = $this->getOptions()->getAddress();
            // only reconnect via tls if we've used tcp before.
            if (preg_match('#tcp://(?<address>.+)#', $previousAddress, $matches)) {
                $this->log('Connecting via TCP failed, now trying to connect via TLS');

                $address = 'tls://' . $matches['address'];
                $this->connected = false;
                $this->getOptions()->setAddress($address);
                $this->getSocket()->reconnect($address);
                $this->connect();
                return;
            }
        }

        throw $exception;
    }

    /**
     * {@inheritDoc}
     */
    public function send($buffer)
    {
        if (false === $this->isConnected()) {
            $this->connect();
        }

        $address = $this->getAddress();
        $this->log("Sending data '$buffer' to '{$address}'", LogLevel::DEBUG);
        $this->getSocket()->write($buffer);
        $this->getOutputStream()->parse($buffer);

        while ($this->checkBlockingListeners()) {
            $this->receive();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function connect()
    {
        if (false === $this->connected) {
            $address = $this->getAddress();
            $this->getSocket()->connect($this->getOptions()->getTimeout());
            $this->getSocket()->setBlocking(true);

            $this->connected = true;
            $this->log("Connected to '{$address}'", LogLevel::DEBUG);
        }

        $this->send(sprintf(static::STREAM_START, XML::quote($this->getOptions()->getTo())));
    }

    /**
     * {@inheritDoc}
     */
    public function disconnect()
    {
        if (true === $this->connected) {
            $address         = $this->getAddress();
            $this->send(static::STREAM_END);
            $this->getSocket()->close();
            $this->connected = false;
            $this->log("Disconnected from '{$address}'", LogLevel::DEBUG);
        }
    }

    /**
     * Get address from options object.
     *
     * @return string
     */
    protected function getAddress()
    {
        return $this->getOptions()->getAddress();
    }

    /**
     * Return socket instance.
     *
     * @return SocketClient
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
}
