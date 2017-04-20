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
 * Class Avatar
 *
 * @see https://xmpp.org/extensions/xep-0084.html#process-pubdata
 * @package Fabiang\Xmpp\Protocol\User
 */
class Avatar implements ProtocolImplementationInterface
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
     * path to image
     * @var null|string
     */
    protected $imagePath;
    /**
     * bin data of image
     *
     * @var null|string
     */
    protected $imageData;

    /**
     * @var string
     */
    protected $imageMime;

    /**
     * @var string
     */
    protected $imageId;

    /**
     * Allowed image mime types
     * @var array
     */
    protected $mimes = array(
        'image/jpeg',
        'image/png',
        'image/gif'
    );

    /**
     * Avatar constructor.
     * @param $from string
     * @param $imagePath string
     */
    public function __construct($from, $imagePath)
    {
        $this->setFrom($from)
            ->setImage($imagePath);
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return XML::quoteMessage(
            "<iq type='set' from='%s' id='%s'>" .
            "<pubsub xmlns='http://jabber.org/protocol/pubsub'>" .
            "<publish node='urn:xmpp:avatar:data'>" .
            "<item id='%s'>" .
            "<data xmlns='urn:xmpp:avatar:data'>" .
            $this->getImageBase64Data() .
            "</data>" .
            "</item>" .
            "</publish>" .
            "</pubsub>" .
            "</iq>",
            $this->getFrom(),
            XML::generateId(),
            $this->getImageId()
        );
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
     * Set jabberID.
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

        $this->getImageMime();
        $this->imageData = file_get_contents($path);
        $this->setImageId();
        return $this;
    }

    /**
     * set SHA1 image id
     */
    protected function setImageId()
    {
        $this->imageId = sha1($this->imageData);
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
     * get converted to Base64 image data
     *
     * @see https://xmpp.org/extensions/xep-0153.html#bizrules-image
     * @return string
     */
    protected function getImageBase64Data()
    {
        return "\n" . wordwrap(XML::base64Encode($this->imageData), 75, "\n", true) . "\n";
    }

    /**
     * get image mime type
     * @return string
     */
    protected function getImageMime()
    {
        if (!$this->imageMime) {
            $size = getimagesize($this->imagePath);
            $this->imageMime = isset($size['mime']) ? $size['mime'] : '';

            if ($size[0] < 32 || $size[1] < 32 || $size[0] > 96 || $size[1] > 96) {
                throw new InvalidArgumentException('Image size must be between 32px and 96px');
            }
            if (!in_array($this->imageMime, $this->mimes)) {
                throw new InvalidArgumentException('Type of Image must be of allowed mimes: ' . implode(', ', $this->mimes) . '.');
            }
        }
        return $this->imageMime;
    }
}