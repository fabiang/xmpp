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

namespace Fabiang\Xmpp\EventListener\Stream\Authentication;

use Fabiang\Xmpp\EventListener\AbstractEventListener;
use Fabiang\Xmpp\Event\XMLEvent;

/**
 * Handler for "digest md5" authentication mechanism.
 *
 * @package Xmpp\EventListener\Authentication
 */
class DigestMd5 extends AbstractEventListener implements AuthenticationInterface
{

    /**
     * Is event blocking stream.
     *
     * @var boolean
     */
    protected $blocking = false;

    /**
     * {@inheritDoc}
     */
    public function attachEvents()
    {
        $input = $this->getConnection()->getInputStream()->getEventManager();
        $input->attach('{urn:ietf:params:xml:ns:xmpp-sasl}challenge', array($this, 'challenge'));

        $output = $this->getConnection()->getOutputStream()->getEventManager();
        $output->attach('{urn:ietf:params:xml:ns:xmpp-sasl}auth', array($this, 'auth'));
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate($username, $password)
    {
        $auth = '<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="DIGEST-MD5"/>';
        $this->getConnection()->send($auth);
    }

    /**
     * Authentication starts -> blocking.
     *
     * @return void
     */
    public function auth()
    {
        $this->blocking = true;
    }

    /**
     * Challenge string received.
     *
     * @param XMLEvent $event XML event
     * @return void
     */
    public function challenge(XMLEvent $event)
    {
        if (false === $event->isStartTag()) {
            list($element) = $event->getParameters();
            $challenge      = $element->nodeValue;
            $this->blocking = false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isBlocking()
    {
        return $this->blocking;
    }

}
