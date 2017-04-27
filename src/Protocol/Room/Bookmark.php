<?php

namespace Fabiang\Xmpp\Protocol\Room;


use Fabiang\Xmpp\Protocol\ProtocolImplementationInterface;
use Fabiang\Xmpp\Util\XML;

/**
 * @see https://xmpp.org/extensions/xep-0048.html#storage-pubsub-upload
 *
 * Class Bookmark
 * @package Fabiang\Xmpp\Protocol\Room
 */
class Bookmark implements ProtocolImplementationInterface
{

    /**
     * user JID
     *
     * @var string
     */
    protected $from;
    /**
     * room JID
     *
     * @var string
     */
    protected $jid;

    /**
     * Set receiver.
     *
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $nickname;

    /**
     * @var bool
     */
    protected $autojoin;

    /**
     * Bookmark constructor.
     * @param string $from - user JID
     * @param string $jid - JID of room
     * @param string $name - name of bookmark
     * @param string $nickname - usernickname in the room
     * @param bool $autojoin - auto login in a room
     */
    public function __construct($from, $jid, $name, $nickname, $autojoin = true)
    {

        $this->setFrom($from)
            ->setJid($jid)
            ->setName($name)
            ->setAutoJoin($autojoin)
            ->setNickname($nickname);
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return XML::quoteMessage(
            "<iq from='%s' type='set' id='%s'>" .
            "<pubsub xmlns='http://jabber.org/protocol/pubsub'>" .
            "<publish node='storage:bookmarks'>" .
            "<item id='current'>" .
            "<storage xmlns='storage:bookmarks'>" .
            "<conference name='%s' jid='%s' autojoin='%s'>" .
            "<nick>%s</nick>" .
            "</conference>" .
            "</storage>" .
            "</item>" .
            "</publish>" .
            "<publish-options>" .
            "<x xmlns='jabber:x:data' type='submit'>" .
            "<field var='FORM_TYPE' type='hidden'>" .
            "<value>http://jabber.org/protocol/pubsub#publish-options</value>" .
            "</field>" .
            "<field var='pubsub#persist_items'>" .
            "<value>true</value>" .
            "</field>" .
            "<field var='pubsub#access_model'>" .
            "<value>whitelist</value>" .
            "</field>" .
            "</x>" .
            "</publish-options>" .
            "</pubsub>" .
            "</iq>",
            $this->getFrom(),
            XML::generateId(),
            $this->getName(),
            $this->getJid(),
            $this->getAutoJoin(),
            $this->getNickname()
        );
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name string
     * @return $this
     */
    public function setName($name)
    {
        $this->name = (string)$name;
        return $this;
    }

    /**
     * Get JabberID.
     *
     * @return string
     */
    public function getJid()
    {
        return $this->jid;
    }

    /**
     * Set jabberID.
     *
     * @param $jid string
     * @return $this
     */
    public function setJid($jid)
    {
        $this->jid = (string)$jid;
        return $this;
    }

    /**
     * @param $autojoin bool
     * @return $this
     */
    public function setAutoJoin($autojoin)
    {
        $this->autojoin = (bool)$autojoin;
        return $this;
    }

    /**
     * @return string
     */
    public function getAutoJoin()
    {
        return $this->autojoin === false ? 'false' : 'true';
    }

    /**
     * Set nickname for chat
     *
     * @param $nickname string
     * @return $this
     */
    public function setNickname($nickname)
    {
        $this->nickname = (string)$nickname;
        return $this;
    }

    /**
     * Get JabberID.
     *
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }
}