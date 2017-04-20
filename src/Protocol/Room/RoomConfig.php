<?php

namespace Fabiang\Xmpp\Protocol\Room;


use Fabiang\Xmpp\Form\FormInterface;
use Fabiang\Xmpp\Protocol\ProtocolImplementationInterface;
use Fabiang\Xmpp\Util\XML;

/**
 * Class CreateReservedRoom
 * @package Fabiang\Xmpp\Protocol\Room
 */
class RoomConfig implements ProtocolImplementationInterface
{
    /**
     * admin user JID
     *
     * @var string
     */
    protected $from;
    /**
     * Set receiver.
     *
     * @var string
     */
    protected $to;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var string - room name
     */
    protected $room;

    public function __construct($from, $to, $room, FormInterface $form)
    {
        $this->setFrom($from)
            ->setTo($to)
            ->setForm($form)
            ->setRoom($room);
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return XML::quoteMessage(
            "<iq from='%s' id='%s' to='%s' type='set' xml:lang='en'>" .
            "<query xmlns='http://jabber.org/protocol/muc#owner'>" .
            "<x xmlns='jabber:x:data' type='submit'/>" .//$this->form->toString() .
            "</query>" .
            "</iq>",
            $this->getFrom(),
            XML::generateId(),
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
     * @param FormInterface $form
     * @return $this
     */
    private function setForm(FormInterface $form)
    {
        $this->form = $form;
        return $this;
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
     * @return string
     */
    public function getRoom(){
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

}