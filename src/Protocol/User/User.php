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

namespace Fabiang\Xmpp\Protocol\User;

use Fabiang\Xmpp\Protocol\Pubsub\BookmarkItem;
use Fabiang\Xmpp\Protocol\Pubsub\PubsubItemInterface;
use Fabiang\Xmpp\Protocol\Pubsub\PubsubSet;

/**
 * User object.
 *
 * @package Xmpp\Protocol
 */
class User
{

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
     *
     * @var string
     */
    protected $subscription;

    /**
     *
     * @var array
     */
    protected $groups = array();

    /**
     * @var array
     */
    protected $pubsubs = array();

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
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
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * @param string $subscription
     * @return $this
     */
    public function setSubscription($subscription)
    {
        $this->subscription = (string)$subscription;
        return $this;
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param array $groups
     * @return $this
     */
    public function setGroups(array $groups)
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * @param $group
     * @return $this
     */
    public function addGroup($group)
    {
        $this->groups[] = (string)$group;
        return $this;
    }

    /**
     * @param BookmarkItem $item
     */
    public function addBookmark(BookmarkItem $item)
    {
        $this->addPubsub(PubsubSet::NODE_BOOKMARKS, $item);
    }

    /**
     * @param $node
     * @param PubsubItemInterface $item
     * @return $this
     */
    public function addPubsub($node, PubsubItemInterface $item)
    {
        if (!array_key_exists($node, $this->pubsubs)) {
            $this->pubsubs[$node] = array();
        }
        array_push($this->pubsubs[$node], $item);
        return $this;
    }

    /**
     * @param $node
     * @return array
     */
    public function getPubsubs($node)
    {
        if (array_key_exists($node, $this->pubsubs)) {
            return $this->pubsubs[$node];
        }
        return array();
    }
}
