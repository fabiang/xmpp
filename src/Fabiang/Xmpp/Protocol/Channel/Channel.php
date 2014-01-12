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

namespace Fabiang\Xmpp\Channel;

use Fabiang\Xmpp\Channel\User;

/**
 * Channel object.
 *
 * @package Xmpp\Channel
 */
class Channel
{

    /**
     * Channel topic.
     *
     * @var string
     */
    protected $topic;

    /**
     * User list.
     *
     * @var User[]
     */
    protected $users = array();

    /**
     * DateTime object which holds join date and time.
     *
     * @var \DateTime
     */
    protected $joined = null;

    /**
     * Channel name.
     *
     * @var string
     */
    protected $channelName;

    public function __construct($channelName, \DateTime $joined = null)
    {
        $this->setChannelName($channelName);
        
        if ($joined) {
            $this->setJoined($joined);
        }
    }

    public function getTopic()
    {
        return $this->topic;
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function getJoined()
    {
        return $this->joined;
    }

    public function getChannelName()
    {
        return $this->channelName;
    }

    public function setTopic($topic)
    {
        $this->topic = $topic;
        return $this;
    }

    public function setUsers(User $users)
    {
        $this->users = $users;
        return $this;
    }

    public function setJoined(\DateTime $joined)
    {
        $this->joined = $joined;
        return $this;
    }

    public function setChannelName($channelName)
    {
        $this->channelName = $channelName;
        return $this;
    }

}
