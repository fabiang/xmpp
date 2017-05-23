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

namespace Updivision\Xmpp\Util;

use Updivision\Xmpp\Exception\ErrorException;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-11-13 at 13:42:23.
 *
 * @coversDefaultClass Updivision\Xmpp\Util\ErrorHandler
 */
class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ErrorHandler
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new ErrorHandler(
            function ($message) {
                trigger_error($message, E_USER_WARNING);
            },
            'unit tests'
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        restore_error_handler();
    }

    /**
     * @covers ::__construct
     * @covers ::execute
     */
    public function testExecute()
    {
        try {
            $this->object->execute($file = __FILE__, $line = __LINE__);
        } catch (ErrorException $exception) {
            $this->assertSame('unit tests', $exception->getMessage());
            $this->assertSame($file, $exception->getFile());
            $this->assertSame($line, $exception->getLine());
            $this->assertSame(E_USER_WARNING, $exception->getSeverity());
            $this->assertSame(0, $exception->getCode());
        }
    }

    /**
     * @covers ::__construct
     * @covers ::execute
     */
    public function testExecuteSuccess()
    {
        $object = new ErrorHandler('trim', ' test');
        $this->assertSame('test', $object->execute('0', 1));
    }

    /**
     * @covers ::__construct
     * @expectedException \Updivision\Xmpp\Exception\InvalidArgumentException
     * @expectedExceptionMessage Argument 1 of "Updivision\Xmpp\Util\ErrorHandler::__construct" must be a callable
     */
    public function testConstructWithWrongType()
    {
        new ErrorHandler(1);
    }
}
