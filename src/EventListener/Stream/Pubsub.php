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
use Fabiang\Xmpp\EventListener\UnBlockingEventListenerInterface;
use Fabiang\Xmpp\Exception\Stream\PubsubErrorException;
use Fabiang\Xmpp\Protocol\Pubsub\BookmarkItem;
use Fabiang\Xmpp\Protocol\Pubsub\PubsubGet;
use Fabiang\Xmpp\Protocol\User\User;

/**
 * pubsub is using in many cases
 *
 * avatars
 *
 * @see https://xmpp.org/extensions/xep-0084.html#process-pubmeta
 *
 * bookmarks
 * @see https://xmpp.org/extensions/xep-0048.html#storage-pubsub-upload
 *
 * Listener
 *
 * @package Xmpp\EventListener
 */
class Pubsub extends AbstractEventListener implements BlockingEventListenerInterface, UnBlockingEventListenerInterface
{
    /**
     * Generated id.
     *
     * @var string
     */
    protected $id;

    /**
     * Blocking.
     *
     * @var boolean
     */
    protected $blocking = false;


    /**
     * {@inheritDoc}
     */
    public function attachEvents()
    {
        $this->getOutputEventManager()
            ->attach('{http://jabber.org/protocol/pubsub}pubsub', array($this, 'query'));
        $this->getInputEventManager()
            ->attach('{jabber:client}iq', array($this, 'result'));
        $this->getInputEventManager()
            ->attach('{http://jabber.org/protocol/pubsub}pubsub', array($this, 'collection'));
        /**
         * errors
         * @see https://xmpp.org/extensions/xep-0060.html#subscriber-retrieve-error
         */
        $this->getInputEventManager()
            ->attach('{http://jabber.org/protocol/pubsub#errors}jid-required', array($this, 'error'));
        $this->getInputEventManager()
            ->attach('{http://jabber.org/protocol/pubsub#errors}subid-required', array($this, 'error'));
        $this->getInputEventManager()
            ->attach('{http://jabber.org/protocol/pubsub#errors}invalid-subid', array($this, 'error'));
        $this->getInputEventManager()
            ->attach('{http://jabber.org/protocol/pubsub#errors}not-subscribed', array($this, 'error'));
        $this->getInputEventManager()
            ->attach('{http://jabber.org/protocol/pubsub#errors}unsupported', array($this, 'error'));
        $this->getInputEventManager()
            ->attach('{http://jabber.org/protocol/pubsub#errors}closed-node', array($this, 'error'));
        $this->getInputEventManager()
            ->attach('{http://jabber.org/protocol/pubsub#errors}not-in-roster-group', array($this, 'error'));
        $this->getInputEventManager()
            ->attach('{http://jabber.org/protocol/pubsub#errors}presence-subscription-required', array($this, 'error'));
    }

    /**
     * @param XMLEvent $event
     */
    public function query(XMLEvent $event)
    {
        $this->blocking = true;
        /* @var $element \DOMElement */
        $element = $event->getParameter(0);
        $this->setId($element->parentNode->getAttribute('id'));
    }

    /**
     * Result received.
     *
     * @param \Fabiang\Xmpp\Event\XMLEvent $event
     *
     * @return void
     */
    public function result(XMLEvent $event)
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
     * @param XMLEvent $event
     */
    public function collection(XMLEvent $event)
    {
        $this->blocking = false;

        $element = $event->getParameter(0);

        if (!$this->getOptions()->getUser()) {
            $this->getOptions()->setUser(new User());
        }
        $user = $this->getOptions()->getUser();

        /**
         * bookmark items
         *
         * @see https://xmpp.org/extensions/xep-0048.html#storage-pubsub-retrieve
         */
        $items = $element->getElementsByTagName('conference');
        if ($items && $items->length > 0) {
            /** @var \DOMElement $item */
            foreach ($items as $item) {
                $bookmark = new BookmarkItem(
                    $item->getAttribute('jid'),
                    $item->getAttribute('name'),
                    $item->getAttribute('autojoin')
                );
                if ($item->firstChild) {
                    $bookmark->setNickname($item->firstChild->textContent);
                }

                $user->addPubsub(PubsubGet::NODE_BOOKMARKS, $bookmark);
            }
        }
    }

    /**
     * we have some errors.
     *
     * @param \Fabiang\Xmpp\Event\XMLEvent $event
     *
     * @return void
     */
    public function error(XMLEvent $event)
    {
        if ($event->isEndTag()) {
            $this->blocking = false;
            throw PubsubErrorException::createFromEvent($event);
        }
    }

    /**
     * Get generated id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set generated id.
     *
     * @param string $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = (string)$id;
    }

    /**
     * {@inheritDoc}
     */
    public function isBlocking()
    {
        return $this->blocking;
    }

    /**
     * {@inheritDoc}
     */
    public function unBlock()
    {
        $this->blocking = false;
    }
}
