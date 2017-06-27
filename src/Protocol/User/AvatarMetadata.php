<?php
/**
 * Created by PhpStorm.
 * User: elfuvo
 * Date: 07.03.17
 * Time: 18:42
 */

namespace Fabiang\Xmpp\Protocol\User;


use Fabiang\Xmpp\Exception\InvalidArgumentException;
use Fabiang\Xmpp\Protocol\ProtocolImplementationInterface;
use Fabiang\Xmpp\Util\XML;

/**
 * Class AvatarMetadata
 *
 * @see https://xmpp.org/extensions/xep-0084.html#process-pubmeta
 * @package Fabiang\Xmpp\Protocol\User
 */
class AvatarMetadata implements ProtocolImplementationInterface
{

    /**
     * admin user JID
     *
     * @var string
     */
    protected $from;
    /**
     * path to image
     * @var null|string
     */
    protected $imagePath;

    /**
     * @var string
     */
    protected $imageMime;

    /**
     * @var string
     */
    protected $imageId;
    /**
     * @var int
     */
    protected $imageWidth;
    /**
     * @var int
     */
    protected $imageHeight;
    /**
     * @var int
     */
    protected $imageSize;

    /**
     * @var string
     */
    protected $url;

    /**
     * AvatarMetadata constructor.
     * @param string $from
     * @param string $imagePath
     * @param string $url
     */
    public function __construct($from, $imagePath, $url = '')
    {
        $this->setFrom($from)
            ->setImage($imagePath)
            ->setUrl($url);
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return XML::quoteMessage(
            "<iq type='set' from='%s' id='%s'>" .
            "<pubsub xmlns='http://jabber.org/protocol/pubsub'>" .
            "<publish node='urn:xmpp:avatar:metadata'>" .
            "<item id='%s'>" .
            "<metadata xmlns='urn:xmpp:avatar:metadata'>" .
            "<info bytes='%s' id='%s' height='%s' type='%s' width='%s' " .
            ($this->getUrl() ? "url='" . $this->getUrl() . "'" : "") .
            "/>" .
            "</metadata>" .
            "</item>" .
            "</publish>" .
            "</pubsub>" .
            "</iq>",
            $this->getFrom(),
            XML::generateId(),
            $this->getImageId(),
            $this->getImageSize(),
            $this->getImageId(),
            $this->getImageHeight(),
            $this->getImageType(),
            $this->getImageWidth()
        );
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
     * set image url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * get image url
     *
     * @param $url
     * @return $this
     */
    public function setUrl($url)
    {
        if (!empty($url)) {
            $this->url = (string)$url;
        }
        return $this;
    }


    /**
     * Set image content
     *
     * @param $path
     * @return $this
     */
    public function setImage($path)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException('File "' . $path . '" does not exists.');
        }
        $this->imagePath = $path;

        $size = getimagesize($this->imagePath);
        $this->imageMime = isset($size['mime']) ? $size['mime'] : '';
        list($this->imageWidth, $this->imageHeight) = $size;
        $this->imageSize = filesize($path);
        $this->imageId = sha1(file_get_contents($path));
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

    /**
     * get image size
     *
     * @return string
     */
    public function getImageSize()
    {
        return (string)$this->imageSize;
    }

    /**
     * get image size
     *
     * @return string
     */
    public function getImageWidth()
    {
        return (string)$this->imageWidth;
    }

    /**
     * get image size
     *
     * @return string
     */
    public function getImageHeight()
    {
        return (string)$this->imageHeight;
    }

    /**
     * get image size
     *
     * @return string
     */
    public function getImageType()
    {
        return (string)$this->imageMime;
    }
}