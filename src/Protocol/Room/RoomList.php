<?php

/**
 * Created by PhpStorm.
 * User: elfuvo
 * Date: 07.03.17
 * Time: 19:18
 */
namespace Fabiang\Xmpp\Protocol\Room;


use Fabiang\Xmpp\Protocol\ProtocolImplementationInterface;
use Fabiang\Xmpp\Util\XML;

/**
 * Class Room
 *
 * list of rooms
 * @package Fabiang\Xmpp\Protocol
 */
class Room implements ProtocolImplementationInterface
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
     * {@inheritDoc}
     */
    public function toString()
    {
        return XML::quoteMessage("<iq from='%s' id='%s' to='%s' type='get'>" .
            "<query xmlns='http://jabber.org/protocol/disco#items'/>" .
            "</iq>",
            $this->getFrom(),
            XML::generateId(),
            $this->getTo()
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
     * Set server address - for example: conference.xmpp.example.org
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
}