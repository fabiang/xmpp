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

namespace Fabiang\Xmpp\Integration;

use Behat\Behat\Context\BehatContext;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Client;
use Fabiang\Xmpp\Connection\Test;
use Fabiang\Xmpp\Connection\Socket;

/**
 * Description of FeatureContext
 *
 * @author f.grutschus
 */
class FeatureContext extends BehatContext
{

    /**
     *
     * @var Client
     */
    protected $client;

    /**
     *
     * @var Options
     */
    protected $options;

    /**
     *
     * @var Test
     */
    protected $connection;

    /**
     * Catch connection exceptions.
     *
     * @var boolean
     */
    protected $catch = false;

    /**
     * Catched exception.
     *
     * @var \Exception
     */
    public $exception;

    /**
     * Constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        /* @var $autoloader \Composer\Autoload\ClassLoader */
        $autoloader = require realpath(__DIR__ . '/../../../vendor/autoload.php');
        $autoloader->add(__NAMESPACE__, __DIR__);

        $this->useContext('authentication', new AuthenticationContext);
        $this->useContext('bind', new BindContext);
        $this->useContext('session', new SessionContext);
        $this->useContext('roster', new RosterContext);
    }

    /**
     * @Given /^Test connection adapter$/
     */
    public function testConnectionAdapter()
    {
        $this->connection = new Test;

        $this->options = new Options;
        $this->options->setTo('localhost');
        $this->options->setConnection($this->connection)
            ->setUsername('aaa')
            ->setPassword('bbb');
        $this->client  = new Client($this->options);
    }

    /**
     * @Given /^Socket connection adapter with address (.+)$/
     */
    public function socketConnectionAdapterWithAddressTcpLocalhost($address)
    {
        $mock = new \Fabiang\Xmpp\Stream\SocketClient($address);

        $this->connection = new Socket($mock);

        $this->options = new Options;
        $this->options->setConnection($this->connection);
        $this->client  = new Client($this->options);
    }

    /**
     * @Given /^URL is (.+)$/
     */
    public function urlIsTcpUnknowenTld($address)
    {
        $this->connection->getOptions()->setAddress($address);
    }

    /**
     * @Given /^Test response data for non-TLS$/
     */
    public function testResponseDataForNonTls()
    {
        $this->connection->setData(array(
            "<?xml version='1.0'?><stream:stream xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams' "
            . "id='1234567890' from='localhost' version='1.0' xml:lang='en'><stream:features></stream:features>"
        ));
    }

    /**
     * @Given /^Test response data for TLS$/
     */
    public function testResponseDataForTls()
    {
        $this->connection->setData(array(
            "<?xml version='1.0'?><stream:stream xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams' "
            . "id='1234567890' from='localhost' version='1.0' xml:lang='en'><stream:features>"
            . '<starttls xmlns="urn:ietf:params:xml:ns:xmpp-tls"/>'
            . "</stream:features>",
            "<proceed xmlns='urn:ietf:params:xml:ns:xmpp-tls'/>",
            "<?xml version='1.0'?><stream:stream xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams' "
            . "id='1234567890' from='localhost' version='1.0' xml:lang='en'><stream:features></stream:features>"
        ));
    }

    /**
     * @Given /^Test response data for disconnect$/
     */
    public function testResponseDataForDisconnect()
    {
        $this->connection->setData(array(
            "<?xml version='1.0'?><stream:stream xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams' "
            . "id='1234567890' from='localhost' version='1.0' xml:lang='en'>",
            '</stream:stream>'
        ));
    }

    /**
     * @Given /^exceptions are catched when connecting$/
     */
    public function exceptionsAreCatchedWhenConnecting()
    {
        $this->catch = true;
    }

    /**
     * @Given /^timeout is set to (\d+) seconds$/
     */
    public function timeoutIsSetToSeconds($timeout)
    {
        $this->getConnection()->getOptions()->setTimeout($timeout);
    }

    /**
     * @When /^connecting/
     */
    public function connecting()
    {
        try {
            $this->connection->connect();
        } catch (\Exception $exception) {
            $this->exception = $exception;
            if (false === $this->catch) {
                throw $exception;
            }
        }
    }

    /**
     * @Then /^should be connected$/
     */
    public function shouldBeConnected()
    {
        assertTrue($this->connection->isConnected());
    }

    /**
     * @Given /^Stream start should be send$/
     * @Then /^Stream start should be send (\d+) times$/
     */
    public function streamStartShouldBeSend($num = 1)
    {
        $expected = sprintf(Socket::STREAM_START, 'localhost');
        $counts   = array_count_values($this->connection->getBuffer());
        assertEquals($num, $counts[$expected]);
    }

    /**
     * @Then /^timeout exception should have been thrown$/
     */
    public function timeoutExceptionShouldHaveThrown()
    {
        assertInstanceOf('\\Fabiang\\Xmpp\\Exception\\TimeoutException', $this->exception);
    }

    /**
     * @Then /^socket exception should have been thrown$/
     */
    public function socketExceptionShouldHaveBeenThrown()
    {
        assertInstanceOf('\\Fabiang\\Xmpp\\Exception\\SocketException', $this->exception);
    }

    /**
     * @When /^disconnecting$/
     */
    public function disconnecting()
    {
        $this->connection->disconnect();
    }

    /**
     * @Then /^Stream end should be send$/
     */
    public function streamEndShouldBeSend()
    {
        assertContains(Socket::STREAM_END, $this->connection->getBuffer());
    }

    /**
     * @Given /^should be disconnected$/
     */
    public function shouldBeDisconnected()
    {
        assertFalse($this->connection->isConnected());
    }

    /**
     * @Then /^Starttls should be send$/
     */
    public function starttlsShouldBeSend()
    {
        assertContains('<starttls xmlns="urn:ietf:params:xml:ns:xmpp-tls"/>', $this->connection->getBuffer());
    }

    /**
     * @Then /^Stream end received$/
     */
    public function streamEndReceived()
    {
        assertContains('</stream:stream>', $this->connection->getData());
    }

    /**
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     *
     * @return Options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

}
