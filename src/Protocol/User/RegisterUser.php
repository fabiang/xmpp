<?php
/**
 * Created by PhpStorm.
 * User: elfuvo
 * Date: 07.03.17
 * Time: 18:42
 */

namespace Fabiang\Xmpp\Protocol\User;


use Fabiang\Xmpp\Protocol\ProtocolImplementationInterface;
use Fabiang\Xmpp\Util\XML;

/**
 * Class RequestUserRegisterForm
 *
 * Register user
 *
 * @see https://xmpp.org/extensions/xep-0133.html#add-user
 * @package Fabiang\Xmpp\Protocol\User
 */
class RegisterUser implements ProtocolImplementationInterface
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
     * new user account JID
     *
     * @var string
     */
    protected $userJid;
    /**
     * new user account password
     *
     * @var string
     */
    protected $password;
    /**
     * SID of registration form
     *
     * @var string
     */
    protected $sid;

    /**
     * RegisterUser constructor.
     * @param $userJid string
     * @param $password string
     * @param $sid string - SID of request form @see RequestUserRegisterForm
     * @param $from string - admin user JID
     * @param null|string $to
     */
    public function __construct($userJid, $password, $sid, $from, $to = null)
    {
        $this->setFrom($from)
            ->setTo($to)
            ->setUserJID($userJid)
            ->setPassword($password)
            ->setSID($sid);
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return XML::quoteMessage(
            "<iq from='%s' id='%s' to='%s' type='set' xml:lang='en'>" .
            "<command xmlns='http://jabber.org/protocol/commands' node='http://jabber.org/protocol/admin#add-user' sessionid='%s'>" .
            "<x xmlns='jabber:x:data' type='submit'>" .
            "<field type='hidden' var='FORM_TYPE'>" .
            "<value>http://jabber.org/protocol/admin</value>" .
            "</field>" .
            "<field var='userJid'>" .
            "<value>%s</value>" .
            "</field>" .
            "<field var='password'>" .
            "<value>%s</value>" .
            "</field>" .
            "<field var='password-verify'>" .
            "<value>%s</value>" .
            "</field>" .
            "</x>" .
            "</command>" .
            "</iq>",
            $this->getFrom(),
            XML::generateId(),
            $this->getTo(),
            $this->getSID(),
            $this->getUserJID(),
            $this->getPassword(),
            $this->getPassword()
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
     * Get UserJID.
     *
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set UserJID.
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
     * @param $sid
     * @return $this
     */
    public function setSID($sid)
    {
        $this->sid = (string)$sid;
        return $this;
    }

    /**
     * @return string
     */
    public function getSID()
    {
        return $this->sid;
    }

    /**
     * Get UserJID.
     *
     * @return string
     */
    public function getUserJID()
    {
        return $this->userJid;
    }

    /**
     * set user account  JID
     *
     * @param $userJid string
     * @return $this
     */
    public function setUserJID($userJid)
    {
        $this->userJid = (string)$userJid;
        return $this;
    }

    /**
     * Get account password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $password string - set account password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = (string)$password;
        return $this;
    }
}