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

use Fabiang\Xmpp\Util\XML;

/**
 * Protocol setting for Xmpp.
 *
 * @package Xmpp\Protocol
 */
class Presence implements ProtocolImplementationInterface
{
    /**
     * Signals that the entity is available for communication.
     */

    const TYPE_AVAILABLE = 'available';

    /**
     * Signals that the entity is no longer available for communication.
     */
    const TYPE_UNAVAILABLE = 'unavailable';

    /**
     * The sender wishes to subscribe to the recipient's presence.
     */
    const TYPE_SUBSCRIBE = 'subscribe';

    /**
     * The sender has allowed the recipient to receive their presence.
     */
    const TYPE_SUBSCRIBED = 'subscribed';

    /**
     * The sender is unsubscribing from another entity's presence.
     */
    const TYPE_UNSUBSCRIBE = 'unsubscribe';

    /**
     * The subscription request has been denied or a previously-granted subscription has been cancelled.
     */
    const TYPE_UNSUBSCRIBED = 'unsubscribed';

    /**
     * A request for an entity's current presence; SHOULD be generated only by a server on behalf of a user.
     */
    const TYPE_PROBE = 'probe';

    /**
     * An error has occurred regarding processing or delivery of a previously-sent presence stanza.
     */
    const TYPE_ERROR = 'error';

    /**
     * The entity or resource is available.
     */
    const SHOW_AVAILABLE = 'available';

    /**
     * The entity or resource is temporarily away.
     */
    const SHOW_AWAY = 'away';

    /**
     * The entity or resource is actively interested in chatting.
     */
    const SHOW_CHAT = 'chat';

    /**
     * The entity or resource is busy (dnd = "Do Not Disturb").
     */
    const SHOW_DND = 'dnd';

    /**
     * The entity or resource is away for an extended period (xa = "eXtended Away").
     */
    const SHOW_XA = 'xa';

    /**
     * Presence to.
     *
     * @var string|null
     */
    protected $to;

    /**
     * Priority.
     *
     * @var integer
     */
    protected $priority = 1;

    /**
     * Nickname for presence.
     *
     * @var string
     */
    protected $nickname;

    /**
     * Constructor.
     *
     * @param integer $priority
     * @param string $to
     * @param string $nickname
     */
    public function __construct($priority = 1, $to = null, $nickname = null)
    {
        $this->setPriority($priority)->setTo($to)->setNickname($nickname);
    }

    /**
     * {@inheritDoc}
     */
    public function toString()
    {
        $presence = '<presence';

        if (null !== $this->getTo()) {
            $presence .= ' to="' . XML::quote($this->getTo()) . '/' . XML::quote($this->getNickname()) . '"';
        }

        return $presence . '><priority>' . $this->getPriority() . '</priority></presence>';
    }

    /**
     * Get nickname.
     *
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * Set nickname.
     *
     * @param string $nickname
     * @return $this
     */
    public function setNickname($nickname)
    {
        $this->nickname = (string) $nickname;
        return $this;
    }

    /**
     * Get to.
     *
     * @return string¦null
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set to.
     *
     * @param string|null $to
     * @return $this
     */
    public function setTo($to = null)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * Get priority.
     *
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set priority.
     *
     * @param integer $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = (int) $priority;
        return $this;
    }
}
