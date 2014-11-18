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

namespace Fabiang\Xmpp\EventListener\Stream;

use Fabiang\Xmpp\Event\XMLEvent;
use Fabiang\Xmpp\Exception\RuntimeException;
use Fabiang\Xmpp\EventListener\Stream\Authentication\AuthenticationInterface;
use Fabiang\Xmpp\Exception\Stream\AuthenticationErrorException;
use Fabiang\Xmpp\EventListener\AbstractEventListener;
use Fabiang\Xmpp\EventListener\BlockingEventListenerInterface;

/**
 * Listener
 *
 * @package Xmpp\EventListener
 */
class Authentication extends AbstractEventListener implements BlockingEventListenerInterface
{

    /**
     * Listener is blocking.
     *
     * @var boolean
     */
    protected $blocking = false;

    /**
     * Collected mechanisms.
     *
     * @var array
     */
    protected $mechanisms = array();

    /**
     * {@inheritDoc}
     */
    public function attachEvents()
    {
        $input = $this->getConnection()->getInputStream()->getEventManager();
        $input->attach('{urn:ietf:params:xml:ns:xmpp-sasl}mechanisms', array($this, 'authenticate'));
        $input->attach('{urn:ietf:params:xml:ns:xmpp-sasl}mechanism', array($this, 'collectMechanisms'));
        $input->attach('{urn:ietf:params:xml:ns:xmpp-sasl}failure', array($this, 'failure'));
        $input->attach('{urn:ietf:params:xml:ns:xmpp-sasl}success', array($this, 'success'));
    }

    /**
     * Collect authentication machanisms.
     *
     * @param XMLEvent $event
     * @return void
     */
    public function collectMechanisms(XMLEvent $event)
    {
        if ($this->getConnection()->isReady() && false === $this->isAuthenticated()) {
            /* @var $element \DOMElement */
            list($element) = $event->getParameters();
            $this->blocking = true;
            if (false === $event->isStartTag()) {
                $this->mechanisms[] = strtolower($element->nodeValue);
            }
        }
    }

    /**
     * Authenticate after collecting machanisms.
     *
     * @param XMLEvent $event
     * @return void
     */
    public function authenticate(XMLEvent $event)
    {
        if ($this->getConnection()->isReady() && false === $this->isAuthenticated() && false === $event->isStartTag()) {
            $this->blocking = true;

            $authentication = $this->determineMechanismClass();

            $authentication->setEventManager($this->getEventManager())
                ->setOptions($this->getOptions())
                ->attachEvents();

            $this->getConnection()->addListener($authentication);
            $authentication->authenticate($this->getOptions()->getUsername(), $this->getOptions()->getPassword());
        }
    }

    /**
     * Determine mechanismclass from collected mechanisms.
     *
     * @return AuthenticationInterface
     * @throws RuntimeException
     */
    protected function determineMechanismClass()
    {
        $authenticationClass = null;

        $authenticationClasses = $this->getOptions()->getAuthenticationClasses();
        foreach ($this->mechanisms as $mechanism) {
            if (array_key_exists($mechanism, $authenticationClasses)) {
                $authenticationClass = $authenticationClasses[$mechanism];
                break;
            }
        }

        if (null === $authenticationClass) {
            throw new RuntimeException('No supportet authentication machanism found.');
        }

        $authentication = new $authenticationClass;

        if (!($authentication instanceof AuthenticationInterface)) {
            $message = 'Authentication class "' . get_class($authentication)
                . '" is no instanceof  AuthenticationInterface';
            throw new RuntimeException($message);
        }

        return $authentication;
    }

    /**
     * Authentication failed.
     *
     * @param XMLEvent $event
     * @throws StreamErrorException
     */
    public function failure(XMLEvent $event)
    {
        if (false === $event->isStartTag()) {
            $this->blocking = false;
            throw AuthenticationErrorException::createFromEvent($event);
        }
    }

    /**
     * Authentication successful.
     *
     * @param XMLEvent $event
     */
    public function success(XMLEvent $event)
    {
        if (false === $event->isStartTag()) {
            $this->blocking = false;

            $connection = $this->getConnection();
            $connection->resetStreams();
            $connection->connect();

            $this->getOptions()->setAuthenticated(true);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isBlocking()
    {
        return $this->blocking;
    }

    /**
     * Get collected mechanisms.
     *
     * @return array
     */
    public function getMechanisms()
    {
        return $this->mechanisms;
    }

    /**
     *
     * @return boolean
     */
    protected function isAuthenticated()
    {
        return $this->getOptions()->isAuthenticated();
    }
}
