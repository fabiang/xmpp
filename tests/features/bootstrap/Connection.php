<?php

namespace Fabiang\Xmpp\Integration;

use Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Client;
use Fabiang\Xmpp\Connection\Test;
use Fabiang\Xmpp\Connection\Socket;

require_once 'PHPUnit/Framework/Assert/Functions.php';

class Connection extends BehatContext
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
     * @Given /^Test connection adapter$/
     */
    public function testConnectionAdapter()
    {
        $this->connection = new Test;

        $this->options = new Options;
        $this->options->setTo('localhost');
        $this->options->setConnection($this->connection);
        $this->client  = new Client($this->options);
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
     * @When /^connecting/
     */
    public function iConnect()
    {
        $this->connection->connect();
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
        $counts = array_count_values($this->connection->getBuffer());
        assertEquals($num, $counts[$expected]);
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
     * @Given /^server should told me to proceed$/
     */
    public function serverShouldToldMeToProceed()
    {
        
    }

}
