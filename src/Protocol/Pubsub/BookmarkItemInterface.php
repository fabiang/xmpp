<?php
namespace Fabiang\Xmpp\Protocol\Pubsub;

interface BookmarkItemInterface{

    /**
     * @param $jid string
     * @return $this
     */
    public function setJid($jid);

    /**
     * @return string
     */
    public function getJid();

    /**
     * @param $autojoin bool
     * @return $this
     */
    public function setAutoJoin($autojoin);

    /**
     * @return string
     */
    public function getAutoJoin();

    /**
     * @param $name string
     * @return $this
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param $nickname string
     * @return $this
     */
    public function setNickname($nickname);

    /**
     * @return string
     */
    public function getNickname();

}