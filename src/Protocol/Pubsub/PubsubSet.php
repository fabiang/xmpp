<?php

namespace Fabiang\Xmpp\Protocol\Pubsub;


use Fabiang\Xmpp\Protocol\ProtocolImplementationInterface;
use Fabiang\Xmpp\Util\XML;

/**
 * Attention! You must publish items only for one node per request!
 *
 * There is a many types of pubsub,
 *
 * but now implemented only Bookmarks
 * @see https://xmpp.org/extensions/xep-0048.html#storage-pubsub-upload
 *
 * Other types not tested
 *
 * Class Bookmark
 * @package Fabiang\Xmpp\Protocol\Room
 */
class PubsubSet implements ProtocolImplementationInterface
{

    /**
     * Node types
     *
     * Bookmark
     * @see https://xmpp.org/extensions/xep-0048.html#storage-pubsub-retrieve
     */
    const NODE_BOOKMARKS = 'storage:bookmarks';

    /**
     * access models
     * @see https://xmpp.org/extensions/xep-0060.html#accessmodels
     */
    const ACCESS_MODEL_OPEN = 'open';
    const ACCESS_MODEL_ROSTER = 'roster';
    const ACCESS_MODEL_PRESENCE = 'presence';
    const ACCESS_MODEL_AUTHORIZE = 'authorize';
    const ACCESS_MODEL_WHITELIST = 'whitelist';

    /**
     * @see https://xmpp.org/extensions/xep-0060.html#events
     */
    const PERSISTENT_NODE = 'true';
    const TRANSIENT_NODE = 'false';

    /**
     * user JID
     *
     * @var string
     */
    protected $from;

    /**
     * pubsub service address
     *
     * @var string
     */
    protected $to;

    /**
     * node name for publishing items
     *
     * @var string
     */
    protected $node;

    /**
     * @var string
     */
    protected $accessModel;
    /**
     * @var string
     */
    protected $persistent;

    /**
     * Bookmark items
     *
     * @var PubsubItemInterface[]
     */
    protected $items = array();

    /**
     * PubsubSet constructor.
     * @param $from
     * @param $node
     * @param null $to
     * @param string $access_model
     * @param string $persistent
     */
    public function __construct($from, $node, $to = null, $access_model = self::ACCESS_MODEL_WHITELIST, $persistent = self::PERSISTENT_NODE)
    {
        $this->setFrom($from)
            ->setNode($node)
            ->setAccessModel($access_model)
            ->setPersistent($persistent);
        if ($to) {
            $this->setTo($to);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return XML::quoteMessage(
            "<iq from='%s' type='set' id='%s'>" .
            "<pubsub xmlns='http://jabber.org/protocol/pubsub'>" .
            $this->composeItems() .
            "<publish-options>" .
            "<x xmlns='jabber:x:data' type='submit'>" .
            "<field var='FORM_TYPE' type='hidden'>" .
            "<value>http://jabber.org/protocol/pubsub#publish-options</value>" .
            "</field>" .
            "<field var='pubsub#persist_items'>" .
            "<value>%s</value>" .
            "</field>" .
            "<field var='pubsub#access_model'>" .
            "<value>%s</value>" .
            "</field>" .
            "</x>" .
            "</publish-options>" .
            "</pubsub>" .
            "</iq>",
            $this->getFrom(),
            XML::generateId(),
            $this->getPersistent(),
            $this->getAccessModel()
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
     * Get server address.
     *
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * set node name
     *
     * @param $node
     * @return $this
     */
    public function setNode($node)
    {
        $this->node = (string)$node;
        return $this;
    }

    /**
     * @return string
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * pubsub service address, for example: pubsub.xmpp.example.org
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
     * @param $model
     * @return $this
     */
    public function setAccessModel($model)
    {

        $this->accessModel = (string)$model;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccessModel()
    {
        return $this->accessModel;
    }

    /**
     * @param $persistent
     * @return $this
     */
    public function setPersistent($persistent)
    {

        $this->persistent = (string)$persistent;
        return $this;
    }

    /**
     * @return string
     */
    public function getPersistent()
    {
        return $this->persistent;
    }

    /**
     * @param $items PubsubItemInterface[]
     * @return $this
     */
    public function setItems($items)
    {
        foreach ($items as $contact) {
            $this->addContact($contact);
        }
        return $this;
    }

    /**
     * @return PubsubItemInterface[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param PubsubItemInterface $contact
     * @return $this
     */
    public function addContact(PubsubItemInterface $contact)
    {
        array_push($this->items, $contact);
        return $this;
    }

    /**
     * compose XML string of items for publishing
     *
     * @return string
     */
    protected function composeItems()
    {
        $result = "<publish node='" . XML::quote($this->getNode()) . "'>";

        foreach ($this->getItems() as $key => $item) {
            // we must set parent XML nodes for collection
            if ($key == 0) {
                $result .= $item->getOuterStartTag();
            }
            $result .= $item->toString();
            // close collection tags
            if (($key + 1) == count($this->getItems())) {
                $result .= $item->getOuterEndTag();
            }
        }
        return $result . '</publish>';
    }
}