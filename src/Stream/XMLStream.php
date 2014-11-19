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

namespace Fabiang\Xmpp\Stream;

use Fabiang\Xmpp\Event\EventManagerAwareInterface;
use Fabiang\Xmpp\Event\EventManagerInterface;
use Fabiang\Xmpp\Event\EventManager;
use Fabiang\Xmpp\Event\XMLEvent;
use Fabiang\Xmpp\Event\XMLEventInterface;
use Fabiang\Xmpp\Exception\XMLParserException;

/**
 * Xml stream class.
 *
 * @package Xmpp\Stream
 */
class XMLStream implements EventManagerAwareInterface
{

    const NAMESPACE_SEPARATOR = ':';

    /**
     * Eventmanager.
     *
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * Document encoding.
     *
     * @var string
     */
    protected $encoding;

    /**
     * Current parsing depth.
     *
     * @var integer
     */
    protected $depth = 0;

    /**
     *
     * @var \DOMDocument
     */
    protected $document;

    /**
     * Collected namespaces.
     *
     * @var array
     */
    protected $namespaces = array();

    /**
     * Cache of namespace prefixes.
     *
     * @var array
     */
    protected $namespacePrefixes = array();

    /**
     * Element cache.
     *
     * @var array
     */
    protected $elements = array();

    /**
     * XML parser.
     *
     * @var resource
     */
    protected $parser;

    /**
     * Event object.
     *
     * @var XMLEventInterface
     */
    protected $eventObject;

    /**
     * Collected events while parsing.
     *
     * @var array
     */
    protected $eventCache = array();

    /**
     * Constructor.
     */
    public function __construct($encoding = 'UTF-8', XMLEventInterface $eventObject = null)
    {
        $this->encoding = $encoding;
        $this->reset();

        if (null === $eventObject) {
            $eventObject = new XMLEvent();
        }

        $this->eventObject = $eventObject;
    }

    /**
     * Free XML parser on desturct.
     */
    public function __destruct()
    {
        xml_parser_free($this->parser);
    }

    /**
     * Parse XML data and trigger events.
     *
     * @param string $source XML source
     * @return \DOMDocument
     */
    public function parse($source)
    {
        $this->clearDocument($source);

        $this->eventCache = array();
        if (0 === xml_parse($this->parser, $source, false)) {
            throw XMLParserException::create($this->parser);
        }
        // trigger collected events.
        $this->trigger();
        $this->eventCache = array();

        // </stream> was not there, so lets close the document
        if ($this->depth > 0) {
            $this->document->appendChild($this->elements[0]);
        }

        return $this->document;
    }

    /**
     * Clear document.
     *
     * Method resets the parser instance if <?xml is found. Overwise it clears the DOM document.
     *
     * @return void
     */
    protected function clearDocument($source)
    {
        $documentElement = $this->document->documentElement;

        // collect xml declaration
        if ('<?xml' === substr($source, 0, 5)) {
            $this->reset();

            $matches = array();
            if (preg_match('/^<\?xml.*encoding=(\'|")([\w-]+)\1.*?>/i', $source, $matches)) {
                $this->encoding = $matches[2];
                xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, $this->encoding);
            }
        } elseif (null !== $documentElement) {
            // clean the document
            /* @var $childNode \DOMNode */
            while ($documentElement->hasChildNodes()) {
                $documentElement->removeChild($documentElement->firstChild);
            }
        }
    }

    /**
     * Starting tag found.
     *
     * @param resource $parser  XML parser
     * @param string   $name    Element name
     * @param attribs  $attribs Element attributes
     * @return void
     */
    protected function startXml()
    {
        list (, $name, $attribs) = func_get_args();

        $elementData = explode(static::NAMESPACE_SEPARATOR, $name, 2);
        $elementName = $elementData[0];
        $prefix      = null;
        if (isset($elementData[1])) {
            $elementName = $elementData[1];
            $prefix      = $elementData[0];
        }

        $attributesNodes = $this->createAttributeNodes($attribs);
        $namespaceAttrib = false;

        // current namespace
        if (array_key_exists('xmlns', $attribs)) {
            $namespaceURI = $attribs['xmlns'];
        } else {
            $namespaceURI = $this->namespaces[$this->depth - 1];
        }

        // namespace of the element
        if (null !== $prefix) {
            $namespaceElement = $this->namespacePrefixes[$prefix];
        } else {
            $namespaceAttrib  = true;
            $namespaceElement = $namespaceURI;
        }

        $this->namespaces[$this->depth] = $namespaceURI;

        // workaround for multiple xmlns defined, since we did have parent element inserted into the dom tree yet
        if (true === $namespaceAttrib) {
            $element = $this->document->createElement($elementName);
        } else {
            $elementNameFull = $elementName;
            if (null !== $prefix) {
                $elementNameFull = $prefix . static::NAMESPACE_SEPARATOR . $elementName;
            }

            $element = $this->document->createElementNS($namespaceElement, $elementNameFull);
        }

        foreach ($attributesNodes as $attributeNode) {
            $element->setAttributeNode($attributeNode);
        }

        $this->elements[$this->depth] = $element;
        $this->depth++;

        $event = '{' . $namespaceElement . '}' . $elementName;
        $this->cacheEvent($event, true, array($element));
    }

    /**
     * Turn attribes into attribute nodes.
     *
     * @param array $attribs Attributes
     * @return array
     */
    protected function createAttributeNodes(array $attribs)
    {
        $attributesNodes = array();
        foreach ($attribs as $name => $value) {
            // collect namespace prefixes
            if ('xmlns:' === substr($name, 0, 6)) {
                $prefix = substr($name, 6);

                $this->namespacePrefixes[$prefix] = $value;
            } else {
                $attribute         = $this->document->createAttribute($name);
                $attribute->value  = $value;
                $attributesNodes[] = $attribute;
            }
        }
        return $attributesNodes;
    }

    /**
     * End tag found.
     *
     * @return void
     */
    protected function endXml()
    {
        $this->depth--;

        $element = $this->elements[$this->depth];

        if ($this->depth > 0) {
            $parent = $this->elements[$this->depth - 1];
        } else {
            $parent = $this->document;
        }
        $parent->appendChild($element);

        $localName = $element->localName;

        // Frist: try to get the namespace from element.
        $namespaceURI = $element->namespaceURI;

        // Second: loop over namespaces till namespace is not null
        if (null === $namespaceURI) {
            $namespaceURI = $this->namespaces[$this->depth];
        }

        $event = '{' . $namespaceURI . '}' . $localName;
        $this->cacheEvent($event, false, array($element));
    }

    /**
     * Data found.
     *
     * @param resource $parser XML parser
     * @param string   $data   Element data
     * @return void
     */
    protected function dataXml()
    {
        $data = func_get_arg(1);
        if (isset($this->elements[$this->depth - 1])) {
            $element = $this->elements[$this->depth - 1];
            $element->appendChild($this->document->createTextNode($data));
        }
    }

    /**
     * Add event to cache.
     *
     * @param string  $event
     * @param boolean $startTag
     * @param array   $params
     * @return void
     */
    protected function cacheEvent($event, $startTag, $params)
    {
        $this->eventCache[] = array($event, $startTag, $params);
    }

    /**
     * Trigger cached events
     *
     * @return void
     */
    protected function trigger()
    {
        foreach ($this->eventCache as $event) {
            list($event, $startTag, $param) = $event;
            $this->eventObject->setStartTag($startTag);
            $this->getEventManager()->setEventObject($this->eventObject);
            $this->getEventManager()->trigger($event, $this, $param);
        }
    }

    /**
     * Reset class properties.
     *
     * @return void
     */
    public function reset()
    {
        $parser = xml_parser_create($this->encoding);
        xml_set_object($parser, $this);

        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);

        xml_set_element_handler($parser, 'startXml', 'endXml');
        xml_set_character_data_handler($parser, 'dataXml');

        $this->parser            = $parser;
        $this->depth             = 0;
        $this->document          = new \DOMDocument('1.0', $this->encoding);
        $this->namespaces        = array();
        $this->namespacePrefixes = array();
        $this->elements          = array();
    }

    /**
     * Get XML parser resource.
     *
     * @return resource
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * {@inheritDoc}
     */
    public function getEventManager()
    {
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    /**
     * {@inheritDoc}
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $this->events = $events;
        $events->setEventObject($this->getEventObject());
        return $this;
    }

    /**
     * Get event object.
     *
     * @return XMLEventInterface
     */
    public function getEventObject()
    {
        return $this->eventObject;
    }

    /**
     * Set event object.
     *
     * @param XMLEventInterface $eventObject
     * @return $this
     */
    public function setEventObject(XMLEventInterface $eventObject)
    {
        $this->eventObject = $eventObject;
        return $this;
    }
}
