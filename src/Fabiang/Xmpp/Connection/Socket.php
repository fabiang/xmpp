<?php

/**
 * Copyright 2013 Fabian Grutschus. All rights reserved.
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
 * @copyright 2013 Fabian Grutschus. All rights reserved.
 * @license   BSD
 * @link      http://github.com/fabiang/xmpp
 */

namespace Fabiang\Xmpp\Connection;

use Socket\Raw\Socket as StreamSocket;
use Socket\Raw\Factory;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Connection to a stream.
 *
 * @package Xmpp\Connection
 */
class Socket implements SocketConnectionInterface, ConnectionInterface
{

    const DEFAULT_LENGTH = 1024;

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
     * Constructor set default socket instance if no socket was given.
     *
     * @param StreamSocket $socket  Socket instance
     * @param string       $address Server address
     */
    public function __construct(StreamSocket $socket, $address)
    {
        if (null !== $socket) {
            $this->setSocket($socket);
        }

        $this->address = $address;
    }

    /**
     *
     * @param string $address Server address
     * @return Socket
     */
    public static function factory($address)
    {
        $socketFactory = new Factory;
        $scheme = null;
        return new static($socketFactory->createFromString($address, $scheme), $address);
    }

    /**
     * {@inheritDoc}
     */
    public function receive()
    {
        $buffer = $this->getSocket()->read(static::DEFAULT_LENGTH);
        $this->log(LogLevel::DEBUG, "Received buffer '$buffer' from '{$this->address}'");
        return $buffer;
    }

    /**
     * {@inheritDoc}
     */
    public function send($buffer)
    {
        $this->getSocket()->write($buffer);
        $this->log(LogLevel::DEBUG, "Sending data '$buffer' to '{$this->address}'");
    }

    /**
     * {@inheritDoc}
     */
    public function connect()
    {
        $this->getSocket()->connect($this->address);
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
    public function setSocket(StreamSocket $socket)
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

}
