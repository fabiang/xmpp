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
use Fabiang\Xmpp\EventListener\AbstractEventListener;
use Fabiang\Xmpp\EventListener\BlockingEventListenerInterface;
use Fabiang\Xmpp\Util\XML;

/**
 * Listener
 *
 * @package Xmpp\EventListener
 */
class Session extends AbstractEventListener implements BlockingEventListenerInterface
{

    /**
     * Listener is blocking.
     *
     * @var boolean
     */
    protected $blocking = false;

    /**
     * Generated id.
     *
     * @var string
     */
    protected $id;

    /**
     * {@inheritDoc}
     */
    public function attachEvents()
    {
        $input = $this->getConnection()->getInputStream()->getEventManager();
        $input->attach('{urn:ietf:params:xml:ns:xmpp-session}session', array($this, 'session'));
        $input->attach('{jabber:client}iq', array($this, 'iq'));
    }

    /**
     * Handle session event.
     *
     * @param XMLEvent $event
     * @return void
     */
    public function session(XMLEvent $event)
    {
        if ($event->isEndTag()) {
            /* @var $element \DOMElement */
            $element = $event->getParameter(0);

            // bind element occured in <features>
            if ('features' === $element->parentNode->localName) {
                $this->blocking = true;
                $this->getConnection()->send(sprintf(
                    '<iq type="set" id="%s"><session xmlns="urn:ietf:params:xml:ns:xmpp-session"/></iq>',
                    $this->getId()
                ));
            }
        }
    }

    /**
     * Handle iq event.
     *
     * @param XMLEvent $event
     * @retrun void
     */
    public function iq(XMLEvent $event)
    {
        if ($event->isEndTag()) {
            /* @var $element \DOMElement */
            $element = $event->getParameter(0);
            if ($this->getId() === $element->getAttribute('id')) {
                $this->blocking = false;
            }
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
     * Get generated id.
     *
     * @return string
     */
    public function getId()
    {
        if (null === $this->id) {
            $this->id = XML::generateId();
        }

        return $this->id;
    }

    /**
     * Set generated id.
     *
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = (string) $id;
        return $this;
    }
}
