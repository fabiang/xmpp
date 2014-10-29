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
class Message implements ProtocolImplementationInterface
{
    /**
     * Chat between to users.
     */

    const TYPE_CHAT = 'chat';

    /**
     * Chat in a multi-user channel (MUC).
     */
    const TYPE_GROUPCHAT = 'groupchat';

    /**
     * Message type.
     *
     * @var string
     */
    protected $type = self::TYPE_CHAT;

    /**
     * Set message receiver.
     *
     * @var string
     */
    protected $to;

    /**
     * Message.
     *
     * @var string
     */
    protected $message = '';

    /**
     * Constructor.
     *
     * @param string $message
     * @param string $to
     * @param string $type
     */
    public function __construct($message = '', $to = '', $type = self::TYPE_CHAT)
    {
        $this->setMessage($message)->setTo($to)->setType($type);
    }

    /**
     * {@inheritDoc}
     */
    public function toString()
    {
        return XML::quoteMessage(
            '<message type="%s" id="%s" to="%s"><body>%s</body></message>',
            $this->getType(),
            XML::generateId(),
            $this->getTo(),
            $this->getMessage()
        );
    }

    /**
     * Get message type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set message type.
     *
     * See {@link self::TYPE_CHAT} and {@link self::TYPE_GROUPCHAT}
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get message receiver.
     *
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set message receiver.
     *
     * @param string $to
     * @return $this
     */
    public function setTo($to)
    {
        $this->to = (string) $to;
        return $this;
    }

    /**
     * Get message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set message.
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = (string) $message;
        return $this;
    }
}
