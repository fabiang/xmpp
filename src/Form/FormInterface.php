<?php

/**
 * Created by PhpStorm.
 * User: elfuvo
 * Date: 15.04.17
 * Time: 9:44
 */

namespace Fabiang\Xmpp\Form;

use Fabiang\Xmpp\Event\XMLEvent;

/**
 * Interface FormInterface
 * @package Fabiang\Xmpp\Form
 */
interface FormInterface
{

    /**
     * parse DOMnode to the form fields
     *
     * @param XMLEvent $event
     * @return mixed
     */
    public function __construct(XMLEvent $event);

    /**
     * returns title of form
     *
     * @return string
     */
    public function getTitle();

    /**
     * get instructions for form filling
     *
     * @return string
     */
    public function getInstructions();

    /**
     * get sessionid of form
     *
     * @return string
     */
    public function getSid();

    /**
     * get fields of form
     *
     * @return array
     */
    public function getFieldNames();

    /**
     * set value of a field
     *
     * @param $fieldName string
     * @param $value string
     * @return bool
     */
    public function setFieldValue($fieldName, $value);

    /**
     * add multiple value for a field
     *
     * @param $fieldName string
     * @param $value string
     * @return bool
     */
    public function addFieldValue($fieldName, $value);

    /**
     * get field attributes, e.g. type, name, label or required
     *
     * @param $field
     * @return array|null
     */
    public function getFieldAttributes($field);

    /**
     * converts form to XML string
     *
     * @return string
     */
    public function toString();

}