<?php
/**
 * Created by PhpStorm.
 * User: elfuvo
 * Date: 15.04.17
 * Time: 9:51
 */

namespace Fabiang\Xmpp\Form;

use Fabiang\Xmpp\Event\XMLEvent;

/**
 * Class AbstractForm
 * @package Fabiang\Xmpp\Form
 */
class AbstractForm implements FormInterface
{
    /**
     * @var string|null
     */
    protected $sid;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $instructions;

    /**
     * @var \DOMElement[]
     */
    protected $fields = [];

    /**
     * @var \DOMElement
     */
    private $form;

    /**
     * get sessionid of form
     *
     * @return string
     */
    public function getSid()
    {
        return '';
    }

    /**
     * returns title of form
     *
     * @return string
     */
    public function getTitle()
    {
        return (string)$this->title;
    }

    /**
     * get instructions for form filling
     *
     * @return string
     */
    public function getInstructions()
    {
        return (string)$this->instructions;
    }

    /**
     * get fields of form
     *
     * @return array
     */
    public function getFieldNames()
    {
        return array_keys($this->fields);
    }

    /**
     * set value of a field
     *
     * @param $fieldName string
     * @param $value string
     * @return bool
     */
    public function setFieldValue($fieldName, $value)
    {
        if (isset($this->fields[$fieldName])) {
            $valueNode = $this->form->ownerDocument->createElement('value', (string)$value);
            // remove previous value
            while ($this->fields[$fieldName]->hasChildNodes()) {
                $this->fields[$fieldName]->removeChild($this->fields[$fieldName]->firstChild);
            }
            // set new value
            $this->fields[$fieldName]->appendChild($valueNode);
            return true;
        }
        return false;
    }

    /**
     * add multiple value for a field
     *
     * @param $fieldName string
     * @param $value string
     * @return bool
     */
    public function addFieldValue($fieldName, $value)
    {
        if (isset($this->fields[$fieldName])) {
            $valueNode = $this->form->ownerDocument->createElement('value', (string)$value);
            // add new value
            $this->fields[$fieldName]->appendChild($valueNode);
            return true;
        }
        return false;
    }

    /**
     * get field attributes, e.g. type, name, label or required
     *
     * @param $field
     * @return array|null
     */
    public function getFieldAttributes($field)
    {
        if (isset($this->fields[$field])) {
            $attributes = [];
            if ($type = $this->fields[$field]->getAttribute('type')) {
                $attributes['type'] = (string)$type;
            }
            if ($name = $this->fields[$field]->getAttribute('var')) {
                $attributes['name'] = (string)$name;
            }
            if ($label = $this->fields[$field]->getAttribute('label')) {
                $attributes['label'] = (string)$label;
            }
            if ($required = $this->fields[$field]->getElementsByTagName('required')) {
                $attributes['required'] = $required->length > 0 ? true : false;
            }
            return $attributes;
        }
        return null;
    }

    /**
     * converts form to XML string
     *
     * @return string
     */
    public function toString()
    {
        $this->form->removeAttribute('status');
        $this->form->firstChild->setAttribute('type', 'submit');
        // remove previous values
        while ($this->form->firstChild->hasChildNodes()) {
            $this->form->firstChild->removeChild($this->form->firstChild->firstChild);
        }
        // append new values
        foreach ($this->fields as $field) {
            if ($field->getAttribute('type') && $field->getAttribute('type') != 'hidden') {
                $field->removeAttribute('type');
            }
            if ($field->getAttribute('label')) {
                $field->removeAttribute('label');
            }
            $this->form->firstChild->appendChild($field);
        }

        return $this->form->ownerDocument->saveXML($this->form);
    }

    /**
     * parse XMLEvent to the form fields
     *
     * AbstractForm constructor.
     * @param XMLEvent $event
     */
    public function __construct(XMLEvent $event)
    {
        /** @var $event \DOMElement */
        $this->form = $event->getParameter(0);
        if ($this->sid = $this->form->getAttribute('sessionid')) {
            $titleNode = $this->form->getElementsByTagName('title')->item(0);
            if ($titleNode) {
                $this->title = $titleNode->nodeValue;
            }
            unset($titleNode);
            $instructionsNode = $this->form->getElementsByTagName('instructions')->item(0);
            if ($instructionsNode) {
                $this->instructions = $instructionsNode->nodeValue;
            }
            unset($instructionsNode);
            $fieldNodeList = $this->form->getElementsByTagName('field');
            if ($fieldNodeList->length > 0) {
                foreach ($fieldNodeList as $field) {
                    /**@var $field \DOMElement */
                    $this->fields[$field->getAttribute('var')] = $field;
                }
            } else {
                $this->fields = [];
            }
            unset($fieldNodeList);
        }
    }
}