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

namespace Fabiang\Xmpp\Connection;

use Fabiang\Xmpp\Stream\XMLStream;
use Fabiang\Xmpp\Event\EventManagerAwareInterface;
use Fabiang\Xmpp\EventListener\EventListenerInterface;
use Fabiang\Xmpp\OptionsAwareInterface;

/**
 * Connections must implement this interface.
 *
 * @package Xmpp\Connection
 */
interface ConnectionInterface extends EventManagerAwareInterface, OptionsAwareInterface
{
    /**
     * Connect.
     *
     * @return void
     */
    public function connect();

    /**
     * Disconnect.
     *
     * @return void
     */
    public function disconnect();
    
    /**
     * Set stream is ready.
     * 
     * @param boolean $flag Flag
     * @return $this
     */
    public function setReady($flag);

    /**
     * Is stream ready.
     * 
     * @return boolean
     */
    public function isReady();

    /**
     * Is connection established.
     *
     * @return boolean
     */
    public function isConnected();
    
    /**
     * Receive data.
     *
     * @return string
     */
    public function receive();

    /**
     * Send data.
     *
     * @param string $buffer Data to send.
     * @return void
     */
    public function send($buffer);

    /**
     * Get output stream.
     *
     * @return XMLStream
     */
    public function getOutputStream();

    /**
     * Get input stream.
     *
     * @return XMLStream
     */
    public function getInputStream();

    /**
     * Set output stream.
     *
     * @param XMLStream $outputStream Output stream
     * @return $this
     */
    public function setOutputStream(XMLStream $outputStream);

    /**
     * Set input stream.
     *
     * @param XMLStream $inputStream Input stream
     * @return $this
     */
    public function setInputStream(XMLStream $inputStream);
    
    /**
     * Reset streams.
     * 
     * @return void
     */
    public function resetStreams();

    /**
     * Add listener.
     *
     * @param EventListenerInterface $eventListener
     * @return $this
     */
    public function addListener(EventListenerInterface $eventListener);
}
