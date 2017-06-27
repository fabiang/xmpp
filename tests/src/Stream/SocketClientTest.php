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

use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-11-17 at 19:10:27.
 *
 * @coversDefaultClass Fabiang\Xmpp\Stream\SocketClient
 */
class SocketClientTest extends TestCase
{

    /**
     * @var SocketClient
     */
    protected $object;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var resource
     */
    protected $server;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->path    = sys_get_temp_dir() . '/phpunit_' . uniqid();
        //touch($this->path);
        $this->address = 'unix://' . $this->path;
        $this->server  = stream_socket_server($this->address);
        $this->assertInternalType('resource', $this->server);

        $this->object = new SocketClient($this->address);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unlink($this->path);
        fclose($this->server);
    }

    /**
     * @covers ::connect
     * @covers ::__construct
     * @covers ::getResource
     * @uses Fabiang\Xmpp\Util\ErrorHandler
     */
    public function testConnect()
    {
        $this->object->connect(0);
        $stream = $this->object->getResource();
        $this->assertInternalType('resource', $stream);
        $this->assertSame('stream', get_resource_type($stream));
    }

    /**
     * @covers ::connect
     * @covers ::__construct
     * @covers ::getResource
     * @uses Fabiang\Xmpp\Util\ErrorHandler
     */
    public function testConnectPersistent()
    {
        $this->object->connect(0, true);
        $stream = $this->object->getResource();
        $this->assertInternalType('resource', $stream);
        $this->assertSame('persistent stream', get_resource_type($stream));
    }

    /**
     * @covers ::reconnect
     * @covers ::getAddress
     * @uses Fabiang\Xmpp\Stream\SocketClient::__construct
     * @uses Fabiang\Xmpp\Stream\SocketClient::getResource
     * @uses Fabiang\Xmpp\Stream\SocketClient::connect
     * @uses Fabiang\Xmpp\Stream\SocketClient::close
     * @uses Fabiang\Xmpp\Util\ErrorHandler
     */
    public function testReconnect()
    {
        $this->object->connect(0);
        $oldResource = $this->object->getResource();

        fclose($this->server);
        unlink($this->path);

        $this->path    = sys_get_temp_dir() . '/phpunit_' . uniqid();
        $this->address = 'unix://' . $this->path;
        $this->server  = stream_socket_server($this->address);
        $this->assertInternalType('resource', $this->server);
        $this->object->reconnect($this->address, 0, true);
        $this->assertSame('persistent stream', get_resource_type($this->object->getResource()));
        $this->assertSame($this->address, $this->object->getAddress());
        $this->assertNotSame($oldResource, $this->object->getResource());
    }

    /**
     * @covers ::close
     * @uses Fabiang\Xmpp\Stream\SocketClient::__construct
     * @uses Fabiang\Xmpp\Stream\SocketClient::getResource
     * @uses Fabiang\Xmpp\Stream\SocketClient::connect
     * @uses Fabiang\Xmpp\Util\ErrorHandler
     */
    public function testClose()
    {
        $this->object->connect(0);
        $this->object->close();

        $this->assertSame('unknown', strtolower(get_resource_type($this->object->getResource())));
    }

    /**
     * @covers ::read
     * @uses Fabiang\Xmpp\Util\ErrorHandler
     * @uses Fabiang\Xmpp\Stream\SocketClient::__construct
     * @uses Fabiang\Xmpp\Stream\SocketClient::connect
     */
    public function testRead()
    {
        $this->object->connect(0);
        $conn = stream_socket_accept($this->server);
        fwrite($conn, 'test');
        $this->assertSame('test', $this->object->read());
    }

    /**
     * @covers ::write
     * @uses Fabiang\Xmpp\Stream\SocketClient::__construct
     * @uses Fabiang\Xmpp\Stream\SocketClient::connect
     * @uses Fabiang\Xmpp\Util\ErrorHandler
     */
    public function testWrite()
    {
        $this->object->connect(0);
        $conn = stream_socket_accept($this->server);
        $this->object->write('test');
        $this->assertSame('test', fread($conn, 4));

        $this->object->write('test', 3);
        $this->assertSame('tes', fread($conn, 4));
    }
}
