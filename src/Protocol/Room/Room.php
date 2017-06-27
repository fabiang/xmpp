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

namespace Fabiang\Xmpp\Protocol\Room;

/**
 * Room object.
 *
 * @package Xmpp\Protocol
 */
class Room
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
     * @see https://xmpp.org/extensions/xep-0045.html#registrar-statuscodes
     */
    // user role has been changed
    const STATUS_AFFILIATION_CHANGED = 101;
    // user is presence in room
    const STATUS_PRESENCE = 110;
    // new room has been created
    const STATUS_CREATED = 201;

    const STATUS_BANNED = 301;
    const STATUS_KICKED = 307;


    const CONFIG_YES = 1;
    const CONFIG_NO = 0;


    /**
     * @var array
     */
    protected $statuses = array();

    /**
     *
     * @var string
     */
    protected $affiliation;

    /**
     *
     * @var string
     */
    protected $name;

    /**
     *
     * @var string
     */
    protected $jid;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return $this
     */
    public function setName($name = null)
    {
        if (null === $name || '' === $name) {
            $this->name = null;
        } else {
            $this->name = $name;
        }
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
     * @param $affiliation
     * @return $this
     */
    public function setAffiliation($affiliation)
    {
        $this->affiliation = $affiliation;
        return $this;
    }

    /**
     * current user is owner of room
     *
     * @return bool
     */
    public function isOwner()
    {
        return $this->affiliation == self::AFFILIATION_OWNER;
    }

    /**
     * current user is admin of room
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->affiliation == self::AFFILIATION_ADMIN;
    }

    /**
     * current user is member of room
     *
     * @return bool
     */
    public function isMember()
    {
        return $this->affiliation != self::AFFILIATION_OUTCAST;
    }

    /**
     * add room status
     *
     * @param $code string
     * @return $this
     */
    public function addStatus($code)
    {
        array_push($this->statuses, (int)$code);
        return $this;
    }

    /**
     * @return bool
     */
    public function isJustCreated()
    {
        return in_array(self::STATUS_CREATED, $this->statuses);
    }
}
