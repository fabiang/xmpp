<?php

namespace Fabiang\Xmpp\Integration;

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Exception\PendingException;

require_once 'PHPUnit/Framework/Assert/Functions.php';

class AuthenticationContext extends BehatContext
{

    /**
     * @Given /^Test response data for plain$/
     */
    public function testResponseDataForPlain()
    {
        $this->getConnection()->setData(array(
            "<?xml version='1.0'?><stream:stream xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams' "
            . "id='1234567890' from='localhost' version='1.0' xml:lang='en'><stream:features>"
            . "<mechanisms xmlns='urn:ietf:params:xml:ns:xmpp-sasl'><mechanism>PLAIN</mechanism></mechanisms>"
            . "</stream:features>",
            "<success xmlns='urn:ietf:params:xml:ns:xmpp-sasl'/>",
            "<?xml version='1.0'?><stream:stream xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams' "
            . "id='1234567890' from='localhost' version='1.0' xml:lang='en'><stream:features></stream:features>"
        ));
    }

    /**
     * @Then /^plain authentication element should be send$/
     */
    public function plainAuthenticationElementShouldBeSend()
    {
        assertContains(
            '<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="PLAIN">AGFhYQBiYmI=</auth>',
            $this->getConnection()->getBuffer()
        );
    }

    /**
     * @Given /^should be authenticated$/
     */
    public function shouldBeAuthenticated()
    {
        assertTrue($this->getConnection()->getOptions()->isAuthenticated());
    }

    /**
     *
     * @return \Fabiang\Xmpp\Connection\Test
     */
    public function getConnection()
    {
        return $this->getMainContext()->getConnection();
    }

}
