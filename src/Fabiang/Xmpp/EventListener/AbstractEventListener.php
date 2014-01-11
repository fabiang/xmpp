<?php

/**
 * Copyright 2013 Fabian Grutschus. All rights reserved.
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
 * @copyright 2013 Fabian Grutschus. All rights reserved.
 * @license   BSD
 * @link      http://github.com/fabiang/xmpp
 */

namespace Fabiang\Xmpp\EventListener;

use Fabiang\Xmpp\Connection\ConnectionInterface;
use Fabiang\Xmpp\Event\EventManagerInterface;

/**
 * Abstract implementaion of event listener
 *
 * @package Xmpp\EventListener
 */
abstract class AbstractEventListener implements EventListenerInterface
{

    /**
     * Event manager instance.
     *
     * @var EventManagerInterface
     */
    protected $inputEventManager;

    /**
     * Event manager instance.
     *
     * @var EventManagerInterface
     */
    protected $outputEventManager;

    /**
     * Connection.
     *
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * {@inheritDoc}
     */
    public function setInputEventListener(EventManagerInterface $events)
    {
        $this->inputEventManager = $events;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setOutputEventListener(EventManagerInterface $events)
    {
        $this->outputEventManager = $events;
        return $this;
    }

    /**
     *
     * @return EventManagerInterface
     */
    public function getInputEventManager()
    {
        return $this->inputEventManager;
    }

    /**
     *
     * @return EventManagerInterface
     */
    public function getOutputEventManager()
    {
        return $this->outputEventManager;
    }

    /**
     * {@inheritDoc}
     */
    public function setConnection(ConnectionInterface $connection)
    {
        $this->connection = $connection;
        return $this;
    }

}
