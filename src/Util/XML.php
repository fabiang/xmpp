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

namespace Fabiang\Xmpp\Util;

/**
 * XML utility methods.
 *
 * @package Xmpp\Util
 */
class XML
{

    /**
     * Quote XML string.
     *
     * @param string $string   String to be quoted
     * @param string $encoding Encoding used for quotation
     * @return string
     */
    public static function quote($string, $encoding = 'UTF-8')
    {
        $flags = ENT_QUOTES;

        if (defined('ENT_XML1')) {
            $flags |= ENT_XML1;
        }

        return htmlspecialchars($string, $flags, $encoding);
    }

    /**
     * Replace variables in a string and quote them before.
     *
     * <b>Hint:</b> this function works like <code>sprintf</code>
     *
     * @param string $message
     * @param mixed  $args
     * @param mixed  $...
     * @return string
     */
    public static function quoteMessage($message)
    {
        $variables = func_get_args();

        // shift message variable
        array_shift($variables);

        // workaround for `static` call in a closure
        $class = __CLASS__;

        return vsprintf(
            $message,
            array_map(
                function ($var) use ($class) {
                    return $class::quote($var);
                },
                $variables
            )
        );
    }

    /**
     * Generate a unique id.
     *
     * @return string
     */
    public static function generateId()
    {
        return static::quote('fabiang_xmpp_' . uniqid());
    }

    /**
     * Encode a string with Base64 and quote it.
     *
     * @param string $data
     * @param string $encoding
     * @return string
     */
    public static function base64Encode($data, $encoding = 'UTF-8')
    {
        return static::quote(base64_encode($data), $encoding);
    }

    /**
     * Decode a Base64 encoded string.
     *
     * @param string $data
     * @return string
     */
    public static function base64Decode($data)
    {
        return base64_decode($data);
    }
}
