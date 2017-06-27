<?php
/**
 * Created by PhpStorm.
 * User: elfuvo
 * Date: 07.03.17
 * Time: 20:28
 */

namespace Fabiang\Xmpp\Protocol\User;


use Fabiang\Xmpp\Protocol\ProtocolImplementationInterface;
use Fabiang\Xmpp\Util\XML;

/**
 * Class VCardPresence
 * Broadcast of changing vCard
 * @see https://xmpp.org/extensions/xep-0153.html#publish
 * @package Fabiang\Xmpp\Protocol\User
 */
class VCardPresence implements ProtocolImplementationInterface
{
    /**
     * @var string
     */
    protected $imageId;

    /**
     * VCardPresence constructor.
     * @param string $imageId
     */
    public function __construct($imageId = '')
    {
        $this->imageId = $imageId;
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        if ($this->imageId) {
            return XML::quoteMessage("<presence>" .
                "<x xmlns='vcard-temp:x:update'>" .
                "<photo>%s</photo>" .
                "</x>" .
                "<x xmlns='jabber:x:avatar'>" .
                "<hash>%s</hash>" .
                "</x>" .
                "</presence>",
                $this->getImageId(),
                $this->getImageId()
            );
        } else {
            return XML::quoteMessage("<presence>" .
                "<x xmlns='vcard-temp:x:update'/>" .
                "</presence>");
        }
    }

    /**
     * set SHA1 image id
     *
     * @param $imageId
     * @return $this
     */
    protected function setImageId($imageId)
    {
        $this->imageId = $imageId;
        return $this;
    }

    /**
     * get image SHA1 id
     *
     * @return string
     */
    public function getImageId()
    {
        return $this->imageId;
    }
}