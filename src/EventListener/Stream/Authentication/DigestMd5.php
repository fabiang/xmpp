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

namespace Fabiang\Xmpp\EventListener\Stream\Authentication;

use Fabiang\Xmpp\EventListener\AbstractEventListener;
use Fabiang\Xmpp\Event\XMLEvent;
use Fabiang\Xmpp\Util\XML;
use Fabiang\Xmpp\Exception\Stream\AuthenticationErrorException;

/**
 * Handler for "digest md5" authentication mechanism.
 *
 * @package Xmpp\EventListener\Authentication
 */
class DigestMd5 extends AbstractEventListener implements AuthenticationInterface
{

    /**
     * Is event blocking stream.
     *
     * @var boolean
     */
    protected $blocking = false;

    /**
     *
     * @var string
     */
    protected $username;

    /**
     *
     * @var string
     */
    protected $password;

    /**
     * {@inheritDoc}
     */
    public function attachEvents()
    {
        $input = $this->getInputEventManager();
        $input->attach('{urn:ietf:params:xml:ns:xmpp-sasl}challenge', array($this, 'challenge'));
        $input->attach('{urn:ietf:params:xml:ns:xmpp-sasl}success', array($this, 'success'));

        $output = $this->getOutputEventManager();
        $output->attach('{urn:ietf:params:xml:ns:xmpp-sasl}auth', array($this, 'auth'));
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate($username, $password)
    {
        $this->setUsername($username)->setPassword($password);
        $auth = '<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="DIGEST-MD5"/>';
        $this->getConnection()->send($auth);
    }

    /**
     * Authentication starts -> blocking.
     *
     * @return void
     */
    public function auth()
    {
        $this->blocking = true;
    }

    /**
     * Challenge string received.
     *
     * @param XMLEvent $event XML event
     * @return void
     */
    public function challenge(XMLEvent $event)
    {
        if ($event->isEndTag()) {
            list($element) = $event->getParameters();

            $challenge = XML::base64Decode($element->nodeValue);
            $values    = $this->parseCallenge($challenge);

            if (isset($values['nonce'])) {
                $send = '<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl">'
                    . $this->response($values) . '</response>';
            } elseif (isset($values['rspauth'])) {
                $send = '<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl"/>';
            } else {
                throw new AuthenticationErrorException("Error when receiving challenge: \"$challenge\"");
            }

            $this->getConnection()->send($send);
        }
    }

    /**
     * Generate response data.
     *
     * @param array $values
     */
    protected function response($values)
    {
        $values['cnonce'] = uniqid(mt_rand(), false);
        $values['nc']     = '00000001';
        $values['qop']    = 'auth';

        if (!isset($values['realm'])) {
            $values['realm'] = $this->getOptions()->getTo();
        }

        if (!isset($values['digest-uri'])) {
            $values['digest-uri'] = 'xmpp/' . $this->getOptions()->getTo();
        }

        $a1 = sprintf('%s:%s:%s', $this->getUsername(), $values['realm'], $this->getPassword());

        if ('md5-sess' === $values['algorithm']) {
            $a1 = pack('H32', md5($a1)) . ':' . $values['nonce'] . ':' . $values['cnonce'];
        }

        $a2 = "AUTHENTICATE:" . $values['digest-uri'];

        $password = md5($a1) . ':' . $values['nonce'] . ':' . $values['nc'] . ':'
            . $values['cnonce'] . ':' . $values['qop'] . ':' . md5($a2);
        $password = md5($password);

        $response = sprintf(
            'username="%s",realm="%s",nonce="%s",cnonce="%s",nc=%s,qop=%s,digest-uri="%s",response=%s,charset=utf-8',
            $this->getUsername(),
            $values['realm'],
            $values['nonce'],
            $values['cnonce'],
            $values['nc'],
            $values['qop'],
            $values['digest-uri'],
            $password
        );

        return XML::base64Encode($response);
    }

    /**
     * Parse challenge string and return its values as array.
     *
     * @param string $challenge
     * @return array
     */
    protected function parseCallenge($challenge)
    {
        if (!$challenge) {
            return array();
        }

        $matches = array();
        preg_match_all('#(\w+)\=(?:"([^"]+)"|([^,]+))#', $challenge, $matches);
        list(, $variables, $quoted, $unquoted) = $matches;
        // filter empty strings; preserve keys
        $quoted   = array_filter($quoted);
        $unquoted = array_filter($unquoted);
        // replace "unquoted" values into "quoted" array and combine variables array with it
        return array_combine($variables, array_replace($quoted, $unquoted));
    }

    /**
     * Handle success event.
     *
     * @return void
     */
    public function success()
    {
        $this->blocking = false;
    }

    /**
     * {@inheritDoc}
     */
    public function isBlocking()
    {
        return $this->blocking;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }
}
