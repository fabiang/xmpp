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

class RosterContext extends BehatContext
{
    
    /**
     * @Given /^Test response data for roster request$/
     */
    public function testResponseDataForRosterRequest()
    {
        $this->getConnection()->setData(array(
            "<?xml version='1.0'?>"
            . "<stream:stream xmlns='jabber:client' xmlns:stream='http://etherx.jabber.org/streams'>",
            "<stream:features></stream:features>",
            "<iq from='test@localhost' to='test@localhost/1234567890' id='fabiang_xmpp_52dfea36bbc83' type='result'>"
            . "<query xmlns='jabber:iq:roster'>"
            . "<item subscription='both' name='John Doe' jid='john.doe@localhost'>"
            . "<group>MyGroup</group>"
            . "<group>MyOtherGroup</group>"
            . "</item>"
            . "</query>"
            . "</iq>"
        ));
    }

    /**
     * @Given /^Roster request send$/
     */
    public function rosterRequestSend()
    {
        $this->getConnection()->send(
            '<iq type="get" id="fabiang_xmpp_1234"><query xmlns="jabber:iq:roster"/></iq>'
        );
    }
    
    /**
     * @Then /^options object should contain roster data$/
     */
    public function optionsObjectShouldContainRosterData()
    {
        $users = $this->getConnection()->getOptions()->getUsers();
        assertCount(1, $users);
        /* @var $user \Fabiang\Xmpp\Protocol\User\User */
        $user = $users[0];
        assertSame('John Doe', $user->getName());
        assertSame('john.doe@localhost', $user->getJid());
        assertSame('both', $user->getSubscription());
        assertSame(array('MyGroup', 'MyOtherGroup'), $user->getGroups());
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
