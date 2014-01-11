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

namespace Fabiang\Xmpp\Protocol;

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

}
