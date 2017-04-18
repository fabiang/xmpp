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
 * Class VCardUpdate
 *
 * update user vCard
 *
 * @see https://xmpp.org/extensions/xep-0153.html#publish
 * @package Fabiang\Xmpp\Protocol\User
 */
class VCardUpdate implements ProtocolImplementationInterface
{

    /**
     * user JID
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
     * vCard properties
     *
     * @var array
     */
    protected $vCard = [];
    /**
     * available properties for set to vCard
     *
     * @var array
     */
    protected $availableProperties = [
        'GIVEN',
        'FAMILY',
        'MIDDLE',
        'PHOTO',
        'EMAIL',
        'JABBERID',
        'NICKNAME',
        'URL',
        'DESC',
    ];
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
    protected $mimes = [
        'image/jpeg',
        'image/png',
        'image/gif'
    ];

    /**
     * VCardUpdate constructor.
     * @param $from string
     */
    public function __construct($from)
    {
        $this->setFrom($from);
        $this->setProperty('JABBERID', $from);
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return XML::quoteMessage("<iq type='set' id='%s'>" .
            "<vCard xmlns='vcard-temp'>" .
            $this->composeVCard() .
            "</vCard>" .
            "</iq>",
            XML::generateId()
        );
    }

    /**
     * compose XML string of vCard
     * @return string
     */
    protected function composeVCard()
    {
        $xmlVCard = '';
        if (!empty($this->vCard)) {
            foreach ($this->vCard as $attrName => $attrValue) {
                // DOMNode maybe?
                $xmlVCard .= '<' . $attrName . '>';
                if (is_array($attrValue)) {
                    foreach ($attrValue as $subAttrName => $subAttrValue) {
                        $xmlVCard .= '<' . $subAttrName . '>';
                        $xmlVCard .= (string)$subAttrValue;
                        $xmlVCard .= '</' . $subAttrName . '>';
                    }
                } else {
                    $xmlVCard .= (string)$attrValue;
                }
                $xmlVCard .= '</' . $attrName . '>';
            }
        }
        return $xmlVCard;
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
     * set property of vCard
     *
     * @param $property
     * @param $value
     * @return $this
     */
    public function setProperty($property, $value)
    {
        if (in_array($property, $this->availableProperties)) {
            switch ($property) {
                case 'GIVEN':
                case 'FAMILY':
                case 'MIDDLE':
                    if (!isset($this->vCard['N'])) {
                        $this->vCard['N'] = array('FAMILY' => '', 'GIVEN' => '', 'MIDDLE' => '');
                        $this->vCard['FN'] = '';
                    }
                    $this->vCard['N'][$property] = (string)$value;
                    $this->vCard['FN'] .= empty($this->vCard['FN']) ? (string)$value : ' ' . (string)$value;
                    break;
                case 'URL':
                case 'NICKNAME':
                case 'JABBERID':
                case 'DESC':
                    $this->vCard[$property] = strip_tags($value);
                    break;
                case 'EMAIL':
                    $this->vCard[$property]['USERID'] = strip_tags($value);
                    break;
                case 'PHOTO':
                    // value must be path to image
                    $this->setImage($value);
                    $this->vCard['PHOTO'] = array('TYPE' => $this->getImageMime(), 'BINVAL' => $this->getImageBase64Data());
                    // free some memory
                    $this->imageData = null;
                    break;
            }
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

        if (!in_array($this->getImageMime(), $this->mimes)) {
            throw new InvalidArgumentException('Type of Image must be of allowed mimes: ' . implode(', ', $this->mimes) . '.');
        }

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
     * todo: check image size: 32-96px, file size: 8096 b,
     * extensions: image/png;image/gif;image/jpeg
     * get image mime type
     * @return string
     */
    protected function getImageMime()
    {
        if (!$this->imageMime) {
            $size = getimagesize($this->imagePath);
            $this->imageMime = isset($size['mime']) ? $size['mime'] : '';
        }
        return $this->imageMime;
    }


}