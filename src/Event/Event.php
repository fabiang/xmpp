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

namespace Fabiang\Xmpp\Event;

use Fabiang\Xmpp\Exception\OutOfRangeException;
use Fabiang\Xmpp\Exception\InvalidArgumentException;

/**
 * Generic event.
 *
 * @package Xmpp\Event
 */
class Event implements EventInterface
{

    /**
     * Event name.
     *
     * @var string
     */
    protected $name;

    /**
     * Target object.
     *
     * @var object
     */
    protected $target;

    /**
     * Event parameters.
     *
     * @var array
     */
    protected $parameters = array();

    /**
     * Event stack.
     *
     * @var array
     */
    protected $eventStack = array();

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = array_values($parameters);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getEventStack()
    {
        return $this->eventStack;
    }

    /**
     * {@inheritDoc}
     */
    public function setEventStack(array $eventStack)
    {
        $this->eventStack = $eventStack;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getParameter($index)
    {
        $parameters = $this->getParameters();

        if (!is_int($index)) {
            throw new InvalidArgumentException(
                'Argument #1 of "' . __CLASS__ . '::' . __METHOD__ . '" must be an integer'
            );
        }

        if (!array_key_exists($index, $parameters)) {
            throw new OutOfRangeException("The offset $index is out of range.");
        }

        return $parameters[$index];
    }
}
