<?php
/**
 * Created by PhpStorm.
 * User: elfuvo
 * Date: 07.03.17
 * Time: 20:28
 */

namespace Fabiang\Xmpp\Protocol\Pubsub;


use Fabiang\Xmpp\Protocol\ProtocolImplementationInterface;
use Fabiang\Xmpp\Util\XML;

/**
 * @see https://xmpp.org/extensions/xep-0060.html#subscriber-retrieve
 *
 * Class Pubsub
 * @package Fabiang\Xmpp\Protocol\User
 */
class PubsubGet implements ProtocolImplementationInterface
{
    /**
     * @see https://xmpp.org/extensions/xep-0048.html#storage-pubsub-retrieve
     */
    const NODE_BOOKMARKS = 'storage:bookmarks';

    /**
     * user JID
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
     * discover node items
     *
     * @var string
     */
    protected $node;


    /**
     * Pubsub constructor.
     * @param string $from
     * @param string $to
     * @param string $node
     */
    public function __construct($from, $to, $node)
    {
        $this->setFrom($from)
            ->setTo($to)
            ->setNode($node);
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return XML::quoteMessage(
            "<iq type='get' from='%s' " .
            ($this->getTo() ? "to='" . XML::quote($this->getTo()) . "' " : "") .
            "id='%s'>" .
            "<pubsub xmlns='http://jabber.org/protocol/pubsub'>" .
            "<items node='%s'/>" .
            "</pubsub>" .
            "</iq>",
            $this->getFrom(),
            XML::generateId(),
            $this->getNode()
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
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @param string $node
     * @return $this
     */
    public function setNode($node)
    {
        $this->node = (string)$node;
        return $this;
    }
}