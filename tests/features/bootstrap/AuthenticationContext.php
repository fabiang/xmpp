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
     * @Given /^Test response data for authentication failure$/
     */
    public function testResponseDataForAuthenticationFailure()
    {
        $this->getConnection()->setData(array(
            "<?xml version='1.0'?><stream:stream xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams' "
            . "id='1234567890' from='localhost' version='1.0' xml:lang='en'><stream:features>"
            . "<mechanisms xmlns='urn:ietf:params:xml:ns:xmpp-sasl'><mechanism>PLAIN</mechanism></mechanisms>"
            . "</stream:features>",
            "<failure xmlns='urn:ietf:params:xml:ns:xmpp-sasl'><not-authorized/></failure>"
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
     * @Then /^a authorization exception should be catched$/
     */
    public function aAuthorizationExceptionShouldBeCatched()
    {
        /* @var $exception \Exception */
        $exception = $this->getMainContext()->exception;
        assertInstanceOf('\Fabiang\Xmpp\Exception\Stream\AuthenticationErrorException', $exception);
        assertSame('Stream Error: "not-authorized"', $exception->getMessage());
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
