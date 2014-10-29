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

/**
 * Interface for events.
 *
 * @package Xmpp\Event
 */
interface EventInterface
{

    /**
     * Get event name.
     *
     * @return string
     */
    public function getName();

    /**
     * Set event name.
     *
     * @param string $name Event name
     * @return $this
     */
    public function setName($name);

    /**
     * Return calling object.
     *
     * @return object
     */
    public function getTarget();

    /**
     * Set calling object.
     *
     * @param object $target Calling object
     * @return $this
     */
    public function setTarget($target);

    /**
     * Return parameters.
     *
     * @return array
     */
    public function getParameters();

    /**
     * Set parameters.
     *
     * @param array $parameters Parameters
     * @return $this
     */
    public function setParameters(array $parameters);
    
    /**
     * Get a parameter by index.
     * 
     * @param integer $index
     * @retrun mixed
     */
    public function getParameter($index);

    /**
     * Get list of previous called callbacks.
     *
     * @return array
     */
    public function getEventStack();
    
    /**
     * Set event stack.
     * 
     * @param array $stack Event stack
     * @return $this
     */
    public function setEventStack(array $stack);
}
