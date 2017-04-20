<?php

namespace Fabiang\Xmpp\Form;

use Fabiang\Xmpp\Event\XMLEvent;

/**
 * Class RoomForm
 * @package Fabiang\Xmpp\Form
 */
class RoomForm extends AbstractForm implements FormInterface
{

    /**
     * parse XMLEvent to the form fields
     *
     * AbstractForm constructor.
     * @param XMLEvent $event
     */
    public function __construct(XMLEvent $event)
    {
        /** @var \DOMElement $query */
        $query = $event->getParameter(0);
        $form = $query->getElementsByTagName('x')->item(0);
        if ($form) {
            static::$form = $form;
            unset($query);
            $titleNode = static::$form->getElementsByTagName('title')->item(0);
            if ($titleNode) {
                $this->title = $titleNode->nodeValue;
                self::$form->removeChild($titleNode);
            }
            unset($titleNode);
            $instructionsNode = static::$form->getElementsByTagName('instructions')->item(0);
            if ($instructionsNode) {
                $this->instructions = $instructionsNode->nodeValue;
                self::$form->removeChild($instructionsNode);
            }
            unset($instructionsNode);
            $fieldNodeList = static::$form->getElementsByTagName('field');
            if ($fieldNodeList->length > 0) {
                for ($i = 0; $i < $fieldNodeList->length; $i++) {
                    $field = $fieldNodeList->item($i);
                    $this->fields[$field->getAttribute('var')] = $field;
                }
            } else {
                $this->fields = array();
            }
            unset($fieldNodeList);
        }
    }

}