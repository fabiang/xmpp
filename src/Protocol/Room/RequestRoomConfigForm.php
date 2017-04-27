<?php
/**
 * Created by PhpStorm.
 * User: elfuvo
 * Date: 07.03.17
 * Time: 20:40
 */

namespace Fabiang\Xmpp\Protocol\Room;


use Fabiang\Xmpp\Protocol\ProtocolImplementationInterface;
use Fabiang\Xmpp\Util\XML;

/**
 * Class RoomRequestConfig
 * request form for configuring the room
 * user must be owner
 *
 * @see https://xmpp.org/extensions/xep-0045.html#createroom-reserved
 * @package Fabiang\Xmpp\Protocol\Room
 */
class RequestRoomConfigForm implements ProtocolImplementationInterface
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
     * nickname for chat
     *
     * @var string
     */
    protected $nickname;

    /**
     * RoomCreate constructor.
     * @param string $from
     * @param string $to
     * @param string $room
     */
    public function __construct($from, $to, $room)
    {
        $this->setFrom($from)
            ->setTo($to)
            ->setRoom($room);
    }

    /**
     * {@inheritDoc}
     */
    public function toString()
    {
        return XML::quoteMessage("<iq from='%s' id='%s' to='%s' type='get'>" .
            "<query xmlns='http://jabber.org/protocol/muc#owner'/>" .
            "</iq>",
            $this->getFrom(),
            Xml::generateId(),
            $this->getFullRoomName()
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
     * Get server address.
     *
     * @return string
     */
    public function getFullRoomName()
    {
        return $this->room . '@' . $this->to;
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
}