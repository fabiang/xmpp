<?php

namespace Fabiang\Xmpp\Protocol;

use Fabiang\Xmpp\Util\XML;

/**
 * Protocol setting for Xmpp.
 *
 * @package Xmpp\Protocol
 */
class Register implements ProtocolImplementationInterface
{

    protected $to;

    protected $from;

    protected $step;

    protected $accountjid;

    protected $password;

    protected $sid;
    /**
     * Constructor.
     *
     * @param integer $priority
     * @param string $to
     * @param string $nickname
     */
    public function __construct($to = null, $from = null, $step = 'one')
    {
        $this->setTo($to);
        $this->setFrom($from);
        $this->setStep($step);
    }

    /**
     * {@inheritDoc}
     */
    public function toString()
    {
        $req ='';

        if($this->step == 'one')
        {
            $req = XML::quoteMessage(
                "<iq from='%s' id='%s' to='%s' type='set' xml:lang='en'>
                    <command xmlns='http://jabber.org/protocol/commands' action='execute' node='http://jabber.org/protocol/admin#add-user'/>
                </iq>",
                $this->getFrom(),
                XML::generateId(),
                $this->getTo()
            );
        }
        else
        {
            $req = XML::quoteMessage(
                "<iq from='%s' id='%s' to='%s' type='set' xml:lang='en'>
                    <command xmlns='http://jabber.org/protocol/commands' node='http://jabber.org/protocol/admin#add-user' sessionid='%s'>
                        <x xmlns='jabber:x:data' type='submit'>
                          <field type='hidden' var='FORM_TYPE'>
                            <value>http://jabber.org/protocol/admin</value>
                          </field>
                          <field var='accountjid'>
                            <value>%s</value>
                          </field>
                          <field var='password'>
                            <value>%s</value>
                          </field>
                          <field var='password-verify'>
                            <value>%s</value>
                          </field>
                        </x>
                    </command>
                </iq>",
                $this->getFrom(),
                XML::generateId(),
                $this->getTo(),
                $this->getSID(),
                $this->getJabberID(),
                $this->getPassword(),
                $this->getPassword()
            );
        }

        return $req;
    }

    /**
     * Get JabberID.
     *
     * @return string
     */
    public function getJabberID()
    {
        return $this->accountjid;
    }

    /**
     * Set abberID.
     *
     * @param string $nickname
     * @return $this
     */
    public function setJabberID($accountjid)
    {
        $this->accountjid = (string) $accountjid;
        return $this;
    }

    /**
     * Get JabberID.
     *
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set abberID.
     *
     * @param string $nickname
     * @return $this
     */
    public function setTo($to)
    {
        $this->to = (string) $to;
        return $this;
    }

    /**
     * Get JabberID.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set abberID.
     *
     * @param string $nickname
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = (string) $password;
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
     * Set abberID.
     *
     * @param string $nickname
     * @return $this
     */
    public function setFrom($from)
    {
        $this->from = (string) $from;
        return $this;
    }

    public function setStep($step)
    {
        $this->step = (string) $step;
        return $this;
    }

    public function setSID($sid)
    {
        $this->sid = (string) $sid;
        return $this;
    }

    public function getSID()
    {
        return $this->sid;
    }
}
