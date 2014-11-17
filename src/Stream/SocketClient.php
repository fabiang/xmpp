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

namespace Fabiang\Xmpp\Stream;

use Fabiang\Xmpp\Exception\InvalidArgumentException;
use Fabiang\Xmpp\Util\ErrorHandler;

/**
 * Stream functions wrapper class.
 *
 * @package Xmpp\Stream
 */
class SocketClient
{

    const BUFFER_LENGTH = 4096;

    /**
     * Resource.
     *
     * @var resource
     */
    protected $resource;

    /**
     * Address.
     *
     * @var string
     */
    protected $address;

    /**
     * Constructor takes address as argument.
     *
     * @param string $address
     */
    public function __construct($address)
    {
        $this->address = $address;
    }

    /**
     * Connect.
     *
     * @param integer $timeout    Timeout for connection
     * @param boolean $persistent Persitent connection
     * @return void
     */
    public function connect($timeout = 30, $persistent = false)
    {
        if (true === $persistent) {
            $flags = STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT;
        } else {
            $flags = STREAM_CLIENT_CONNECT;
        }

        // call stream_socket_client with custom error handler enabled
        $handler = new ErrorHandler(
            function ($address, $timeout, $flags) {
                return stream_socket_client($address, $errno, $errstr, $timeout, $flags);
            },
            $this->address,
            $timeout,
            $flags
        );
        $resource = $handler->execute(__FILE__, __LINE__);

        stream_set_timeout($resource, $timeout);
        $this->resource = $resource;
    }

    /**
     * Reconnect and optionally use different address.
     *
     * @param string  $address
     * @param integer $timeout
     * @param bool    $persistent
     */
    public function reconnect($address = null, $timeout = 30, $persistent = false)
    {
        $this->close();

        if (null !== $this->address) {
            $this->address = $address;
        }

        $this->connect($timeout, $persistent);
    }

    /**
     * Close stream.
     *
     * @return void
     */
    public function close()
    {
        fclose($this->resource);
    }

    /**
     * Set stream blocking mode.
     *
     * @param boolean $flag Flag
     * @return $this
     */
    public function setBlocking($flag = true)
    {
        stream_set_blocking($this->resource, (int) $flag);
        return $this;
    }

    /**
     * Read from stream.
     *
     * @param integer $length Bytes to read
     * @return string
     */
    public function read($length = self::BUFFER_LENGTH)
    {
        return fread($this->resource, $length);
    }

    /**
     * Write to stream.
     *
     * @param string  $string String
     * @param integer $length Limit
     * @return void
     */
    public function write($string, $length = null)
    {
        if (null !== $length) {
            fwrite($this->resource, $string, $length);
        } else {
            fwrite($this->resource, $string);
        }
    }

    /**
     * Enable/disable cryptography on stream.
     *
     * @param boolean $enable     Flag
     * @param integer $cryptoType One of the STREAM_CRYPTO_METHOD_* constants.
     * @return void
     * @throws InvalidArgumentException
     */
    public function crypto($enable, $cryptoType = null)
    {
        if (false === $enable) {
            $handler = new ErrorHandler('stream_socket_enable_crypto', $this->resource, false);
            return $handler->execute(__FILE__, __LINE__);
        }

        if (null === $cryptoType) {
            throw new InvalidArgumentException('Second argument is require when enabling crypto an stream');
        }

        return stream_socket_enable_crypto($this->resource, $enable, $cryptoType);
    }

    /**
     * Get socket stream.
     *
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Return address.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }
}
