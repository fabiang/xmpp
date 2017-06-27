<?php

namespace Fabiang\Xmpp\Protocol\Pubsub;

interface PubsubItemInterface
{
    /**
     * return XML representation
     *
     * @return string
     */
    public function toString();

    /**
     * parse XML representation to BookmarkItem
     *
     * @param \DOMElement $item
     * @return $this
     */
    public static function parseItem(\DOMElement $item);

    /**
     * @return string
     */
    public function getOuterStartTag();

    /**
     * @return string
     */
    public function getOuterEndTag();
}