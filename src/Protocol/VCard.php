<?php

namespace Fabiang\Xmpp\Protocol;

use Fabiang\Xmpp\Util\XML;

/**
 * Protocol setting for Xmpp.
 *
 * @package Xmpp\Protocol
 */
class VCard implements ProtocolImplementationInterface
{

    /**
     * vCard to.
     *
     * @var string|null
     */
    protected $to;


    /**
     * user firstname.
     *
     * @var string
     */
    protected $firstname;

    /**
     * user lastname.
     *
     * @var string
     */
    protected $lastname;

    protected $jabberid;

    protected $mime;

    protected $image;

    protected $ulr;

    /**
     * Constructor.
     *
     * @param integer $priority
     * @param string $to
     * @param string $nickname
     */
    public function __construct($firstname = null, $lastname = null,  $jabberid = null)
    {
        $this->setFirstname($firstname);
        $this->setLastname($lastname);
        $this->setJabberID($jabberid);
    }

    /**
     * {@inheritDoc}
     */
    public function toString()
    {

         return XML::quoteMessage(
            '<iq id="' . XML::generateId() . '" type="set">
              <vCard xmlns="vcard-temp">
                <FN>%s</FN>
                <N>
                  <FAMILY>%s</FAMILY>
                  <GIVEN>%s</GIVEN>
                  <MIDDLE/>
                </N>
                <NICKNAME>%s</NICKNAME>
                <URL>%s</URL>
                <PHOTO>
                  <TYPE>%s</TYPE>
                  <BINVAL>
                    %s
                  </BINVAL>
                </PHOTO>
                <JABBERID>%s</JABBERID>
                <DESC/>
              </vCard>
            </iq>',
            $this->getFirstname().' '.$this->getLastname(),
            $this->getLastname(),
            $this->getFirstname(),
            $this->getFirstname().' '.$this->getLastname(),
            $this->getUrl(),
            $this->getMime(),
            $this->getImage(),
            $this->getJabberID()
        );
    }

    /**
     * Get nickname.
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set nickname.
     *
     * @param string $nickname
     * @return $this
     */
    public function setFirstname($firstname)
    {
        $this->firstname = (string) $firstname;
        return $this;
    }

    /**
     * Get nickname.
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set nickname.
     *
     * @param string $nickname
     * @return $this
     */
    public function setLastname($lastname)
    {
        $this->lastname = (string) $lastname;
        return $this;
    }

    /**
     * Get JabberID.
     *
     * @return string
     */
    public function getJabberID()
    {
        return $this->jabberid;
    }

    /**
     * Set abberID.
     *
     * @param string $nickname
     * @return $this
     */
    public function setJabberID($jabberid)
    {
        $this->jabberid = (string) $jabberid;
        return $this;
    }

    /**
     * Get mime.
     *
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * Set mime.
     *
     * @param string $mime
     * @return $this
     */
    public function setMime($mime)
    {
        $this->mime = (string) $mime;
        return $this;
    }

    /**
     * Get image.
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set image.
     *
     * @param string $image base64
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = (string) $image;
        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set url.
     *
     * @param string $image base64
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = (string) $url;
        return $this;
    }
}
