<?php

namespace Fabiang\Xmpp\Protocol\Room;

use Fabiang\Xmpp\Protocol\ProtocolImplementationInterface;
use Fabiang\Xmpp\Util\XML;

/**
 * @see https://xmpp.org/extensions/xep-0045.html#grantmember
 *
 * Class Membership
 * @package Fabiang\Xmpp\Protocol\Room
 */
class Membership implements ProtocolImplementationInterface
{

    /**
     * role of user in MUC
     * @see https://xmpp.org/extensions/xep-0045.html#affil
     */
    const AFFILIATION_OWNER = 'owner';
    const AFFILIATION_ADMIN = 'admin';
    const AFFILIATION_MEMBER = 'member';
    const AFFILIATION_OUTCAST = 'outcast';
    const AFFILIATION_NONE = 'none';

    /**
     * @var string - name of MUC server
     */
    protected $to;

    /**
     * @var string - user JID
     */
    protected $from;

    /**
     * @var string
     */
    protected $affiliation;

    /**
     * new member jid
     *
     * @var string
     */
    protected $jid;

    /**
     * @var string
     */
    protected $nickname;

    /**
     * message when membership/ban
     *
     * @var string
     */
    protected $reason;

    /**
     * Membership constructor.
     * @param string $from - admin JID
     * @param string $to - MUC JID
     * @param $affiliation
     * @param string $jid - member JID
     * @param string $nickname
     * @param string $reason
     */
    public function __construct($from, $to, $affiliation, $jid, $nickname = '', $reason = '')
    {
        if (empty($nickname)) {
            $nickname = preg_replace("#@(.*)#", "", $jid);
        }
        $this->setFrom($from)
            ->setTo($to)
            ->setAffiliation($affiliation)
            ->setJid($jid)
            ->setNickname($nickname)
            ->setReason($reason);
    }

    /**
     * {@inheritDoc}
     */
    public function toString()
    {
        return XML::quoteMessage(
            "<iq from='%s' id='%s' to='%s' type='set'>" .
            "<query xmlns='http://jabber.org/protocol/muc#admin'>" .
            "<item affiliation='%s' jid='%s' nick='%s'>" .
            ($this->getReason() ? "<reason>" . XML::quote($this->getReason()) . "</reason>" : "") .
            "</item>" .
            "</query>" .
            "</iq>",
            $this->getFrom(),
            XML::generateId(),
            $this->getTo(),
            $this->getAffiliation(),
            $this->getJid(),
            $this->getNickname(),
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
     * @return string
     */
    public function getJid()
    {
        return $this->jid;
    }

    /**
     * @param $jid
     * @return $this
     */
    public function setJid($jid)
    {
        $this->jid = (string)$jid;
        return $this;
    }

    /**
     * @return string
     */
    public function getAffiliation()
    {
        return $this->affiliation;
    }

    /**
     * @param $affiliation string
     * @return $this
     */
    public function setAffiliation($affiliation)
    {
        $this->affiliation = (string)$affiliation;
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