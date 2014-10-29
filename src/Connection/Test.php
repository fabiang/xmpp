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

use Fabiang\Xmpp\Util\XML;

/**
 * Connection test double.
 *
 * @package Xmpp\Connection
 */
class Test extends AbstractConnection
{

    /**
     * Data for next receive().
     *
     * @var array
     */
    protected $data = array();

    /**
     * Buffer data.
     *
     * @var array
     */
    protected $buffer = array();

    /**
     * {@inheritDoc}
     */
    public function connect()
    {
        $this->connected = true;
        $this->send(sprintf(
            Socket::STREAM_START,
            XML::quote($this->getOptions()->getTo())
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function disconnect()
    {
        $this->send(Socket::STREAM_END);
        $this->connected = false;
    }

    /**
     * {@inheritDoc}
     */
    public function receive()
    {
        if (!empty($this->data)) {
            $buffer = array_shift($this->data);
            $this->getInputStream()->parse($buffer);
            return $buffer;
        }

        $this->checkTimeout($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function send($buffer)
    {
        $this->buffer[] = $buffer;
        $this->getOutputStream()->parse($buffer);

        while ($this->checkBlockingListeners()) {
            $this->receive();
        }
    }

    /**
     * Set data for next receive().
     *
     * @param string|null $data
     * @return $this
     */
    public function setData(array $data = null)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Return data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get buffer data.
     *
     * @return array
     */
    public function getBuffer()
    {
        return $this->buffer;
    }
}
