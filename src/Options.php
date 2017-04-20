<?php

/**
 * Copyright 2014 Fabian Grutschus. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * The views and conclusions contained in the software and documentation are those
 * of the authors and should not be interpreted as representing official policies,
 * either expressed or implied, of the copyright holders.
 *
 * @author    Fabian Grutschus <f.grutschus@lubyte.de>
 * @copyright 2014 Fabian Grutschus. All rights reserved.
 * @license   BSD
 * @link      http://github.com/fabiang/xmpp
 */

namespace Fabiang\Xmpp;

use Fabiang\Xmpp\Connection\ConnectionInterface;
use Fabiang\Xmpp\EventListener\Stream\Authentication\DigestMd5;
use Fabiang\Xmpp\EventListener\Stream\Authentication\Plain;
use Fabiang\Xmpp\Form\FormInterface;
use Fabiang\Xmpp\Protocol\DefaultImplementation;
use Fabiang\Xmpp\Protocol\ImplementationInterface;
use Fabiang\Xmpp\Protocol\Room\Room;
use Fabiang\Xmpp\Protocol\User\User;
use Psr\Log\LoggerInterface;

/**
 * Xmpp connection options.
 *
 * @package Xmpp
 */
class Options
{

    /**
     *
     * @var ImplementationInterface
     */
    protected $implementation;

    /**
     *
     * @var string
     */
    protected $address;

    /**
     * Connection object.
     *
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * PSR-3 Logger interface.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     *
     * @var string
     */
    protected $to;

    /**
     *
     * @var string
     */
    protected $username;

    /**
     *
     * @var string
     */
    protected $password;

    /**
     *
     * @var string
     */
    protected $jid;

    /**
     *
     * @var FormInterface|null
     */
    protected $form;

    /**
     * verify SSL certificate on connect
     *
     * @var bool
     */
    protected $verifyPeer = true;

    /**
     *
     * @var boolean
     */
    protected $authenticated = false;

    /**
     *
     * @var array
     */
    protected $users = array();

    /**
     *
     * @var null|Protocol\Room\Room
     */
    protected $room;

    /**
     *
     * @var null|User
     */
    protected $user;

    /**
     * Timeout for connection.
     *
     * @var integer
     */
    protected $timeout = 30;

    /**
     * Authentication methods.
     *
     * @var array
     */
    protected $authenticationClasses;

    /**
     * Constructor.
     *
     * @param string $address Server address
     */
    public function __construct($address = null)
    {
        $this->authenticationClasses = array(
            'digest-md5' => DigestMd5::className(),
            'plain' => Plain::className()
        );

        if (null !== $address) {
            $this->setAddress($address);
        }
    }

    /**
     * Get protocol implementation.
     *
     * @return ImplementationInterface
     */
    public function getImplementation()
    {
        if (null === $this->implementation) {
            $this->setImplementation(new DefaultImplementation());
        }

        return $this->implementation;
    }

    /**
     * Set protocol implementation.
     *
     * @param ImplementationInterface $implementation
     * @return $this
     */
    public function setImplementation(ImplementationInterface $implementation)
    {
        $this->implementation = $implementation;
        return $this;
    }

    /**
     * Get server address.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set server address.
     *
     * When a address is passed this setter also calls setTo with the hostname part of the address.
     *
     * @param string $address Server address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = (string)$address;
        if (false !== ($host = parse_url($address, PHP_URL_HOST))) {
            $this->setTo($host);
        }
        return $this;
    }

    /**
     * Get connection object.
     *
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Set connection object.
     *
     * @param ConnectionInterface $connection
     * @return $this
     */
    public function setConnection(ConnectionInterface $connection)
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * Get logger instance.
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Set logger instance.
     *
     * @param \Psr\Log\LoggerInterface $logger PSR-3 Logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Get server name.
     *
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set server name.
     *
     * This value is send to the server in requests as to="" attribute.
     *
     * @param string $to
     * @return $this
     */
    public function setTo($to)
    {
        $this->to = (string)$to;
        return $this;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username.
     *
     * @param string $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = (string)$username;
        return $this;
    }

    /**
     * Get resource.
     *
     * @return string
     */
    public function getResource()
    {
        $username = $this->getUsername();
        $username = explode('/', $username);
        return isset($username[1]) ? $username[1] : '';
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password.
     *
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = (string)$password;
        return $this;
    }

    /**
     * Get users jid.
     *
     * @return string
     */
    public function getJid()
    {
        return $this->jid;
    }

    /**
     * Set users jid.
     *
     * @param string $jid
     * @return $this
     */
    public function setJid($jid)
    {
        $this->jid = (string)$jid;
        return $this;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * set form
     *
     * @param FormInterface $form
     * @return $this
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @param $verify bool
     * @return $this
     */
    public function setVerifyPeer($verify)
    {
        $this->verifyPeer = (bool)$verify;
        return $this;
    }

    /**
     * @return bool
     */
    public function getVerifyPeer()
    {
        return $this->verifyPeer;
    }

    /**
     * Is user authenticated.
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * Set authenticated.
     *
     * @param boolean $authenticated Flag
     * @return $this
     */
    public function setAuthenticated($authenticated)
    {
        $this->authenticated = (bool)$authenticated;
        return $this;
    }

    /**
     * Get users.
     *
     * @return Protocol\User\User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set users.
     *
     * @param array $users User list
     * @return $this
     */
    public function setUsers(array $users)
    {
        $this->users = $users;
        return $this;
    }

    /**
     * Get users.
     *
     * @return Protocol\User\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get room.
     *
     * @return Protocol\Room\Room
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * Set room.
     *
     * @param Room $room
     * @return $this
     */
    public function setRoom(Room $room)
    {
        $this->room = $room;
        return $this;
    }

    /**
     * Get authentication classes.
     *
     * @return array
     */
    public function getAuthenticationClasses()
    {
        return $this->authenticationClasses;
    }

    /**
     *
     * @param array $authenticationClasses Authentication classes
     * @return $this
     */
    public function setAuthenticationClasses(array $authenticationClasses)
    {
        $this->authenticationClasses = $authenticationClasses;
        return $this;
    }

    /**
     * Get timeout for connection.
     *
     * @return integer
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Set timeout for connection.
     *
     * @param integer $timeout Seconds
     * @return \Fabiang\Xmpp\Options
     */
    public function setTimeout($timeout)
    {
        $this->timeout = (int)$timeout;
        return $this;
    }
}
