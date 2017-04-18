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

namespace Fabiang\Xmpp\Exception\Stream;

use Fabiang\Xmpp\Event\XMLEvent;

/**
 * Class CommandErrorException
 * @package Fabiang\Xmpp\Exception\Stream
 */
class CommandErrorException extends StreamErrorException
{
    /**
     * @see https://xmpp.org/extensions/xep-0086.html#sect-idm139696314152720
     */
    const ERROR_UNDEFINED = 0;
    const ERROR_BAD_REQUEST = 400;
    const ERROR_CONFLICT = 409;
    const ERROR_FEATURE_NOT_IMPLEMENTED = 501;
    const ERROR_FORBIDDEN = 403;
    const ERROR_GONE = 302;
    const ERROR_INTERNAL_SERVER_ERROR = 500;
    const ERROR_ITEM_NOT_FOUND = 404;
    const ERROR_NOT_ACCEPTABLE = 406;
    const ERROR_NOT_ALLOWED = 405;
    const ERROR_NOT_AUTHORIZED = 401;
    const ERROR_REGISTRATION_REQUIRED = 407;
    const ERROR_SERVER_TIMEOUT = 504;
    const ERROR_SERVICE_UNAVAILABLE = 503;

    /**
     * Create exception from XMLEvent object.
     *
     * @param \Fabiang\Xmpp\Event\XMLEvent $event XMLEvent object
     *
     * @return static
     */
    public static function createFromEvent(XMLEvent $event)
    {
        /* @var $element \DOMElement */
        list($element) = $event->getParameters();

        /* @var $first \DOMElement */
        $parent = $element->parentNode;

        if (null !== $parent && XML_ELEMENT_NODE === $parent->nodeType) {
            $code = (int)$parent->getAttribute('code');
            $message = 'Stream Error: "' . $element->localName . '"';
        } else {
            $code = 0;
            $message = 'Generic stream error';
        }

        $exception = new static($message, $code);
        $exception->setContent($element->ownerDocument->saveXML($element));

        return $exception;
    }
}