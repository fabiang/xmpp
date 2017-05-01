<?php
/**
 * Created by PhpStorm.
 * User: elfuvo
 * Date: 01.05.17
 * Time: 11:36
 */

namespace Fabiang\Xmpp\Protocol\Pubsub;


use DOMElement;
use Fabiang\Xmpp\Util\XML;

class BookmarkItem implements BookmarkItemInterface, PubsubItemInterface
{
    /**
     * @var string
     */
    protected $jid;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $autojoin = true;

    /**
     * @var string
     */
    protected $nickname = '';

    /**
     * BookmarkItem constructor.
     * @param string $jid
     * @param string $name
     * @param bool $autojoin
     * @param string $nickname
     */
    public function __construct($jid, $name, $autojoin = true, $nickname = '')
    {
        $this->setJid($jid)
            ->setName($name)
            ->setAutoJoin($autojoin)
            ->setNickname($nickname);
    }

    public function toString()
    {
        return XML::quoteMessage(
            "<conference name='%s' jid='%s' autojoin='%s'>" .
            "<nick>%s</nick>" .
            "</conference>",
            $this->getName(),
            $this->getJid(),
            $this->getAutoJoin(),
            $this->getNickname()
        );
    }

    /**
     * @return string
     */
    public function getOuterStartTag()
    {
        return "<item id='current'>" .
            "<storage xmlns='storage:bookmarks'>";
    }

    /**
     * @return string
     */
    public function getOuterEndTag()
    {
        return "</storage>" .
            "</item>";
    }

    /**
     * parse conference node
     *
     * @param DOMElement $item
     * @return $this
     */
    public static function parseItem(DOMElement $item)
    {
        $jid = $item->getAttribute('jid');
        $name = $item->getAttribute('name');
        $autojoin = $item->getAttribute('autojoin') == 'true';

        $nickname = '';
        if ($item->firstChild) {
            $nickname = $item->firstChild->textContent;
        }

        return new self($jid, $name, $autojoin, $nickname);
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