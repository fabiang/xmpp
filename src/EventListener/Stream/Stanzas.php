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
use Fabiang\Xmpp\EventListener\EventListenerInterface;
use Fabiang\Xmpp\EventListener\UnBlockingEventListenerInterface;
use Fabiang\Xmpp\Exception\Stream\StanzasErrorException;

/**
 * Listener
 *
 * @package Xmpp\EventListener
 */
class Stanzas extends AbstractEventListener implements EventListenerInterface
{

    /**
     * {@inheritDoc}
     */
    public function attachEvents()
    {
        /**
         * error events
         * @see https://xmpp.org/extensions/xep-0133.html#errors
         */
        $this->getInputEventManager()
            ->attach('{urn:ietf:params:xml:ns:xmpp-stanzas}bad-request', array($this, 'error'));
        $this->getInputEventManager()
            ->attach('{urn:ietf:params:xml:ns:xmpp-stanzas}conflict', array($this, 'error'));
        $this->getInputEventManager()
            ->attach('{urn:ietf:params:xml:ns:xmpp-stanzas}feature-not-implemented', array($this, 'error'));
        $this->getInputEventManager()
            ->attach('{urn:ietf:params:xml:ns:xmpp-stanzas}forbidden', array($this, 'error'));
        $this->getInputEventManager()
            ->attach('{urn:ietf:params:xml:ns:xmpp-stanzas}not-allowed', array($this, 'error'));
        $this->getInputEventManager()
            ->attach('{urn:ietf:params:xml:ns:xmpp-stanzas}service-unavailable', array($this, 'error'));
        /**
         * MUC error events
         * @see https://xmpp.org/extensions/xep-0045.html#enter-errorcodes
         */
        $this->getInputEventManager()
            ->attach('{urn:ietf:params:xml:ns:xmpp-stanzas}item-not-found', array($this, 'error'));
        $this->getInputEventManager()
            ->attach('{urn:ietf:params:xml:ns:xmpp-stanzas}registration-required', array($this, 'error'));
        $this->getInputEventManager()
            ->attach('{urn:ietf:params:xml:ns:xmpp-stanzas}not-acceptable', array($this, 'error'));
        $this->getInputEventManager()
            ->attach('{urn:ietf:params:xml:ns:xmpp-stanzas}jid-malformed', array($this, 'error'));
    }

    /**
     * we have some errors.
     *
     * @param \Fabiang\Xmpp\Event\XMLEvent $event
     * @return void
     */
    public function error(XMLEvent $event)
    {
        if ($event->isEndTag()) {
            $blockedEvent = $this->getConnection()->getLastBlockingListener();
            if ($blockedEvent instanceof UnBlockingEventListenerInterface) {
                $blockedEvent->unBlock();
            }
            throw StanzasErrorException::createFromEvent($event);
        }
    }

}