<?php

namespace Fabiang\Xmpp\Protocol\Room;

use Fabiang\Xmpp\Protocol\ProtocolImplementationInterface;
use Fabiang\Xmpp\Util\XML;

/**
 * @see https://xmpp.org/extensions/xep-0249.html
 *
 * Class DirectInvite
 * @package Fabiang\Xmpp\Protocol\Room
 */
class DirectInvite implements ProtocolImplementationInterface
{

    /**
     * @var string - name of MUC server
     */
    protected $to;

    /**
     * @var string - user JID
     */
    protected $from;

    /**
     * @var string - room name
     */
    protected $room;

    /**
     * password for the room
     *
     * @var string
     */
    protected $password;

    /**
     * message when inviting
     *
     * @var string
     */
    protected $reason;

    /**
     * DirectInvite constructor.
     * @param $from string
     * @param $to string
     * @param $room string
     * @param string $password
     * @param string $reason
     */
    public function __construct($from, $to, $room, $password = '', $reason = '')
    {
        $this->setFrom($from)
            ->setTo($to)
            ->setRoom($room)
            ->setPassword($password)
            ->setReason($reason);
    }

    /**
     * {@inheritDoc}
     */
    public function toString()
    {
        return XML::quoteMessage("<message from='%s' to='%s'>" .
            "<x xmlns='jabber:x:conference' jid='%s' password='%s' reason='%s'/>" .
            "</message>",
            $this->getFrom(),
            $this->getTo(),
            $this->getRoom(),
            $this->getPassword(),
            $this->getReason()
        );
    }

    /**
     * Get server address.
     *
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set receiver - for example: xmpp.example.org
     *
     * @param $to string
     * @return $this
     */
    public function setTo($to)
    {
        $this->to = (string)$to;
        return $this;
    }

    /**
     * Get server address.
     *
     * @return string
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * Set receiver - for example: xmpp.example.org
     *
     * @param $room string
     * @return $this
     */
    public function setRoom($room)
    {
        $this->room = (string)$room;
        return $this;
    }

    /**
     * Get JabberID.
     *
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set jabberID.
     *
     * @param $from string
     * @return $this
     */
    public function setFrom($from)
    {
        $this->from = (string)$from;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * set room password
     *
     * @param $password string
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = (string)$password;
        return $this;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param $reason string
     * @return $this
     */
    public function setReason($reason)
    {
        $this->reason = (string)$reason;
        return $this;
    }
}