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
use Fabiang\Xmpp\EventListener\Stream\Compression\CompressionInterface;
use Fabiang\Xmpp\Exception\RuntimeException;

/**
 * Listener
 *
 * @package Xmpp\EventListener
 */
class Compression extends AbstractEventListener implements BlockingEventListenerInterface
{

    /**
     * Stream is blocking.
     *
     * @var boolean
     */
    protected $blocking = false;

    /**
     * Compression methods.
     * 
     * @var array
     */
    protected $methods = array();

    /**
     * {@inheritDoc}
     */
    public function attachEvents()
    {
        $input = $this->getConnection()->getInputStream()->getEventManager();
        $input->attach('{http://jabber.org/features/compress}method', array($this, 'collectMethods'));
        $input->attach('{http://jabber.org/features/compress}compression', array($this, 'compressConnection'));
        $input->attach('{http://jabber.org/protocol/compress}compressed', array($this, 'compressed'));
    }

    /**
     * Collect compression methods.
     * 
     * @param XMLEvent $event
     * @return void
     */
    public function collectMethods(XMLEvent $event)
    {
        if ($event->isEndTag()) {
            $this->blocking = true;

            /* @var $element \DOMElement */
            $element = $event->getParameter(0);
            $this->methods[] = $element->nodeValue;
        }
    }

    public function compressConnection(XMLEvent $event)
    {
        if ($event->isEndTag()) {
            $compression = $this->determineMethodClass();
            $compression->setEventManager($this->getEventManager())
                ->setOptions($this->getOptions())
                ->attachEvents();
            
            $this->getConnection()->addListener($compression);
            $compression->compression();
        }
    }
    
    public function compressed(XMLEvent $event)
    {
        if ($event->isEndTag()) {
            $this->blocking = false;
            $connection = $this->getConnection();
            $connection->resetStreams();
            $connection->connect();
        }
    }

    /**
     * Determine which class is handling the compression method.
     * 
     * @return CompressionInterface
     * @throws RuntimeException
     */
    protected function determineMethodClass()
    {
        $compressionClass = null;

        $compressionClasses = $this->getOptions()->getCompressionClasses();
        foreach ($this->methods as $method) {
            if (array_key_exists($method, $compressionClasses)) {
                $compressionClass = $compressionClasses[$method];
                break;
            }
        }

        if (null === $compressionClass) {
            throw new RuntimeException('No supported compression method found.');
        }

        $compression = new $compressionClass;

        if (!($compression instanceof CompressionInterface)) {
            $message = 'Compression class "' . get_class($compression) . '" is no instance of CompressionInterface';
            throw new RuntimeException($message);
        }

        return $compression;
    }

    /**
     * Get compression methods.
     * 
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * {@inheritDoc}
     */
    public function isBlocking()
    {
        return $this->blocking;
    }

}
