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

namespace Fabiang\Xmpp\Protocol;

use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\EventListener\EventListenerInterface;
use Fabiang\Xmpp\Event\EventManagerInterface;
use Fabiang\Xmpp\Event\EventManager;
use Fabiang\Xmpp\EventListener\Stream\Stream;
use Fabiang\Xmpp\EventListener\Stream\StreamError;
use Fabiang\Xmpp\EventListener\Stream\StartTls;
use Fabiang\Xmpp\EventListener\Stream\Authentication;
use Fabiang\Xmpp\EventListener\Stream\Bind;
use Fabiang\Xmpp\EventListener\Stream\Session;
use Fabiang\Xmpp\EventListener\Stream\Roster as RosterListener;
use Fabiang\Xmpp\EventListener\Stream\Register as RegisterListener;
use Fabiang\Xmpp\EventListener\Stream\BlockedUsers as BlockedUsersListener;

/**
 * Default Protocol implementation.
 *
 * @package Xmpp\Protocol
 */
class DefaultImplementation implements ImplementationInterface
{

    /**
     * Options.
     *
     * @var Options
     */
    protected $options;

    /**
     * Eventmanager.
     *
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * {@inheritDoc}
     */
    public function register()
    {
        $this->registerListener(new Stream);
        $this->registerListener(new StreamError);
        $this->registerListener(new StartTls);
        $this->registerListener(new Authentication);
        $this->registerListener(new Bind);
        $this->registerListener(new Session);
        $this->registerListener(new RosterListener);
        $this->registerListener(new RegisterListener);
        $this->registerListener(new BlockedUsersListener);
    }

    /**
     * {@inheritDoc}
     */
    public function registerListener(EventListenerInterface $eventListener)
    {
        $connection = $this->getOptions()->getConnection();

        $eventListener->setEventManager($this->getEventManager())
            ->setOptions($this->getOptions())
            ->attachEvents();

        $connection->addListener($eventListener);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions(Options $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getEventManager()
    {
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    /**
     * {@inheritDoc}
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $this->events = $events;
        return $this;
    }
}
