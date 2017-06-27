<?php
/**
 * Created by PhpStorm.
 * User: elfuvo
 * Date: 15.04.17
 * Time: 9:51
 */

namespace Fabiang\Xmpp\Form;

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
    protected $fields = array();

    /**
     * @var \DOMElement
     */
    protected static $form;

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
            $valueNode = static::$form->ownerDocument->createElement('value', (string)$value);
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
            $valueNode = static::$form->ownerDocument->createElement('value', (string)$value);
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
     * return field options if has
     *
     * @param $field string
     * @return array
     */
    public function getFieldOptions($field)
    {
        $options = array();
        if (isset($this->fields[$field])) {
            $optionNodeList = $this->fields[$field]->getElementsByTagName('option');
            if ($optionNodeList->length > 0) {
                for ($i = 0; $i < $optionNodeList->length; $i++) {
                    $optionNode = $optionNodeList->item($i);
                    array_push($options, $optionNode->firstChild->textContent);
                }
            }
        }
        return $options;
    }

    /**
     * converts form to XML string
     *
     * @return string
     */
    public function toString()
    {
        static::$form->setAttribute('type', 'submit');
        // append new values
        foreach ($this->fields as $field) {
            if ($field->getAttribute('type') && $field->getAttribute('type') != 'hidden') {
                $field->removeAttribute('type');
            }
            if ($field->getAttribute('label')) {
                $field->removeAttribute('label');
            }
            // remove option artifacts
            $optionNodeList = $field->getElementsByTagName('option');
            while ($optionNodeList->length > 0) {
                $field->removeChild($optionNodeList->item(0));
            }

            static::$form->appendChild($field);
        }

        return static::$form->ownerDocument->saveXML(static::$form);
    }

    public function unsetAllFields()
    {
        $this->fields = array();
    }
}