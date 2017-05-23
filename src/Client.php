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

namespace Updivision\Xmpp;

use Updivision\Xmpp\Options;
use Updivision\Xmpp\Connection\ConnectionInterface;
use Updivision\Xmpp\Connection\Socket;
use Updivision\Xmpp\Protocol\ProtocolImplementationInterface;
use Updivision\Xmpp\Event\EventManagerAwareInterface;
use Updivision\Xmpp\Event\EventManagerInterface;
use Updivision\Xmpp\Event\EventManager;
use Updivision\Xmpp\EventListener\Logger;

/**
 * Xmpp connection client.
 *
 * @package Xmpp
 */
class Client implements EventManagerAwareInterface
{

    /**
     * Eventmanager.
     *
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * Options.
     *
     * @var Options
     */
    protected $options;

    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * Constructor.
     *
     * @param Options               $options      Client options
     * @param EventManagerInterface $eventManager Event manager
     */
    public function __construct(Options $options, EventManagerInterface $eventManager = null)
    {
        // create default connection
        if (null !== $options->getConnection()) {
            $connection = $options->getConnection();
        } else {
            $connection = Socket::factory($options);
            $options->setConnection($connection);
        }
        $this->options    = $options;
        $this->connection = $connection;

        if (null === $eventManager) {
            $eventManager = new EventManager();
        }
        $this->eventManager = $eventManager;

        $this->setupImplementation();
    }

    /**
     * Setup implementation.
     *
     * @return void
     */
    protected function setupImplementation()
    {
        $this->connection->setEventManager($this->eventManager);
        $this->connection->setOptions($this->options);

        $implementation = $this->options->getImplementation();
        $implementation->setEventManager($this->eventManager);
        $implementation->setOptions($this->options);
        $implementation->register();

        $implementation->registerListener(new Logger());
    }

    /**
     * Connect to server.
     *
     * @return void
     */
    public function connect()
    {
        $this->connection->connect();
    }

    /**
     * Disconnect from server.
     *
     * @return void
     */
    public function disconnect()
    {
        $this->connection->disconnect();
    }

    /**
     * Send data to server.
     *
     * @param ProtocolImplementationInterface $interface Interface
     * @return void
     */
    public function send(ProtocolImplementationInterface $interface)
    {
        $data = $interface->toString();
        $this->connection->send($data);
    }

    /**
     * {@inheritDoc}
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * {@inheritDoc}
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
        return $this;
    }

    /**
     * Get options.
     *
     * @return Options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
