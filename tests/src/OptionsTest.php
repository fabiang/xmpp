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

namespace Updivision\Xmpp;

use Updivision\Xmpp\Connection\Test;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-01-17 at 09:32:06.
 *
 * @coversDefaultClass Updivision\Xmpp\Options
 */
class OptionsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Options
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->object = new Options;
    }

    /**
     * Test passing addess via constructor.
     *
     * @covers ::__construct
     * @uses Updivision\Xmpp\Options::setAddress
     * @uses Updivision\Xmpp\Options::getAddress
     * @uses Updivision\Xmpp\Options::setTo
     * @return void
     */
    public function testConstructor()
    {
        $address = 'tcp://localhost:1234';
        $object = new Options($address);
        $this->assertSame($address, $object->getAddress());
    }

    /**
     * Test setting and getting implementation.
     *
     * @covers ::getImplementation
     * @covers ::setImplementation
     * @uses Updivision\Xmpp\Options::__construct
     * @return void
     */
    public function testSetAndGetImplementation()
    {
        $this->assertInstanceOf(
            __NAMESPACE__ . '\\Protocol\\DefaultImplementation',
            $this->object->getImplementation()
        );

        $implementation = new Protocol\DefaultImplementation;
        $this->assertSame($implementation, $this->object->setImplementation($implementation)->getImplementation());
    }

    /**
     * Test setting and getting address.
     *
     * @covers ::getAddress
     * @covers ::setAddress
     * @uses Updivision\Xmpp\Options::__construct
     * @uses Updivision\Xmpp\Options::setTo
     * @uses Updivision\Xmpp\Options::getTo
     * @return void
     */
    public function testSetAndGetAddress()
    {
        $address = 'tcp://localhost:1234';
        $this->assertSame($address, $this->object->setAddress($address)->getAddress());
        $this->assertSame('localhost', $this->object->getTo());
    }

    /**
     * Test setting and getting address.
     *
     * @covers ::getConnection
     * @covers ::setConnection
     * @uses Updivision\Xmpp\Options::__construct
     * @return void
     */
    public function testSetAndGetConnection()
    {
        $connection = new Test();
        $this->assertSame($connection, $this->object->setConnection($connection)->getConnection());
    }

    /**
     * Test setting and getting logger.
     *
     * @covers ::getLogger
     * @covers ::setLogger
     * @uses Updivision\Xmpp\Options::__construct
     * @return void
     */
    public function testGetLogger()
    {
        $logger = new \Monolog\Logger('foobar');
        $this->assertSame($logger, $this->object->setLogger($logger)->getLogger());
    }

    /**
     * Test setting and getting to.
     *
     * @covers ::getTo
     * @covers ::setTo
     * @uses Updivision\Xmpp\Options::__construct
     * @return void
     */
    public function testSetAndGetTo()
    {
        $this->assertSame('foobar', $this->object->setTo('foobar')->getTo());
    }

    /**
     * Test setting and getting username.
     *
     * @covers ::getUsername
     * @covers ::setUsername
     * @uses Updivision\Xmpp\Options::__construct
     * @return void
     */
    public function testSetAndGetUsername()
    {
        $this->assertSame('username', $this->object->setUsername('username')->getUsername());
    }

    /**
     * Test setting and getting password.
     *
     * @covers ::getPassword
     * @covers ::setPassword
     * @uses Updivision\Xmpp\Options::__construct
     * @return void
     */
    public function testSetAndGetPassword()
    {
        $this->assertSame('pass', $this->object->setPassword('pass')->getPassword());
    }

    /**
     * Test setting and getting Jid.
     *
     * @covers ::getJid
     * @covers ::setJid
     * @uses Updivision\Xmpp\Options::__construct
     * @return void
     */
    public function testSetAndGetJid()
    {
        $this->assertSame('jid', $this->object->setJid('jid')->getJid());
    }

    /**
     * Test setting and checking authenticated.
     *
     * @covers ::isAuthenticated
     * @covers ::setAuthenticated
     * @uses Updivision\Xmpp\Options::__construct
     * @return void
     */
    public function testSetAndIsAuthenticated()
    {
        $this->assertFalse($this->object->isAuthenticated());
        $this->object->setAuthenticated(1);
        $this->assertTrue($this->object->isAuthenticated());
    }

    /**
     * Test setting and getting users.
     *
     * @covers ::getUsers
     * @covers ::setUsers
     * @uses Updivision\Xmpp\Options::__construct
     * @return void
     */
    public function testSetAndGetUsers()
    {
        $users = array(1, 2, 3);
        $this->assertSame($users, $this->object->setUsers($users)->getUsers());
    }

    /**
     * @covers ::getAuthenticationClasses
     * @covers ::setAuthenticationClasses
     * @uses Updivision\Xmpp\Options::__construct
     * @return void
     */
    public function testSetAndGetAuthenticationClasses()
    {
        $classes = array('plain' => '\stdClass');
        $this->assertSame($classes, $this->object->setAuthenticationClasses($classes)->getAuthenticationClasses());
    }

    /**
     * Test setting and getting timeout.
     *
     * @covers ::setTimeout
     * @covers ::getTimeout
     * @uses Updivision\Xmpp\Options::__construct
     * @return void
     */
    public function testSetAndGetTimeout()
    {
        $this->assertSame(10, $this->object->setTimeout('10')->getTimeout());
    }

}
