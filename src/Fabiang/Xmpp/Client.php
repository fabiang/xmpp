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

namespace Fabiang\Xmpp;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Fabiang\Xmpp\Connection\ConnectionInterface;
use Fabiang\Xmpp\Channel\Channel;
use Fabiang\Xmpp\Event\EventManagerAwareInterface;
use Fabiang\Xmpp\Event\EventManagerInterface;
use Fabiang\Xmpp\Event\EventManager;

/**
 * Xmpp connection client.
 *
 * @package Xmpp
 */
class Client implements EventManagerAwareInterface, LoggerAwareInterface
{
    /**
     * Eventmanager.
     *
     * @var EventManagerInterface
     */
    protected $events;
    

    /**
     * Connection.
     *
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Channel list.
     *
     * @var Channel[]
     */
    protected $channels = array();

    /**
     * Event manager instance.
     *
     * @var Event\EventManager
     */
    protected $inputEventManager;

    /**
     * Event manager instance.
     *
     * @var Event\EventManager
     */
    protected $outputEventManager;

    /**
     *
     * @var Stream\XMLStream
     */
    protected $outputStream;

    /**
     *
     * @var Stream\XMLStream
     */
    protected $inputStream;

    /**
     * Event listeners.
     *
     * @var EventListener\EventListenerInterface[]
     */
    protected $listeners = array();

    /**
     * Constructor.
     *
     * @param \Fabiang\Xmpp\Connection\ConnectionInterface $connection Connection
     * @param \Psr\Log\LoggerInterface                     $logger     Logger instance (optional)
     */
    public function __construct(ConnectionInterface $connection, LoggerInterface $logger = null)
    {
        $this->connection = $connection;
        $this->setLogger($logger);

        $this->outputEventManager = new Event\EventManager;
        $this->outputStream       = new Stream\XMLStream();
        $this->outputStream->setEventManager($this->outputEventManager);

        $this->inputEventManager = new Event\EventManager;
        $this->inputStream       = new Stream\XMLStream();
        $this->inputStream->setEventManager($this->inputEventManager);
    }

    public function connect()
    {
        $this->connection->connect();
    }

    public function disconnect()
    {
        $this->connection->disconnect();
    }

    public function send(Protocol\ProtocolImplementationInterface $interface)
    {
        $data = $interface->toString();
        $this->connection->send($data);
        $this->outputStream->parse($data);

        while ($this->checkBlockingListeners()) {
            $data = $this->connection->receive();
            $this->inputStream->parse($data);
        }
    }

    public function registerListner(EventListener\EventListenerInterface $listener)
    {
        $listener->setEventManager($this->getEventManager());
        $listener->setInputEventListener($this->inputEventManager);
        $listener->setOutputEventListener($this->outputEventManager);
        $listener->setConnection($this->connection);
        $listener->attachEvents();
        $this->listeners[] = $listener;
    }

    protected function checkBlockingListeners()
    {
        foreach ($this->listeners as $listerner) {
            $instanceof = $listerner instanceof EventListener\BlockingEventListenerInterface;
            if ($instanceof && true === $listerner->isBlocking()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->connection->setLogger($logger);
        return $this;
    }

    /**
     * Mapper function for logger interface.
     *
     * See {@link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md} for more
     * information about PSR-3 logging standard.
     *
     * @param string $level   Log level
     * @param string $message Log message
     * @param array  $context Context parameters (optional)
     * @return void
     */
    protected function log($level, $message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
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
