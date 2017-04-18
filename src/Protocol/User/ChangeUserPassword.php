<?php
/**
 *
 */

namespace Fabiang\Xmpp\Protocol\User;


use Fabiang\Xmpp\Form\FormInterface;
use Fabiang\Xmpp\Protocol\ProtocolImplementationInterface;
use Fabiang\Xmpp\Util\XML;

/**
 * Class ChangeUserPassword
 *
 * Change user password
 *
 * @see https://xmpp.org/extensions/xep-0133.html#change-user-password
 * @package Fabiang\Xmpp\Protocol\User
 */
class ChangeUserPassword implements ProtocolImplementationInterface
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
     * @var FormInterface
     */
    protected $form;

    /**
     * ChangeUserPassword constructor.
     * @param $userJid string
     * @param $password string
     * @param $from string
     * @param $to string
     * @param FormInterface $form
     */
    public function __construct($userJid, $password, $from, $to, FormInterface $form)
    {
        $this->setFrom($from)
            ->setTo($to)
            ->setUserJID($userJid)
            ->setPassword($password)
            ->setForm($form);

        $this->form->setFieldValue('accountjid', $this->getUserJID());
        $this->form->setFieldValue('password', $this->getPassword());
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return XML::quoteMessage(
            "<iq from='%s' id='%s' to='%s' type='set' xml:lang='en'>" .
            $this->form->toString() .
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
     * @param FormInterface $form
     * @return $this
     */
    private function setForm(FormInterface $form)
    {
        $this->form = $form;
        return $this;
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