<?php

/*
 * Copyright 2010 Pablo Díez <pablodip@gmail.com>
 *
 * This file is part of Mandango.
 *
 * Mandango is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Mandango is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Mandango. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mandango\Tests\Logger;

use Mandango\Logger\LoggableMongo;

class LoggableMongoTest extends \PHPUnit_Framework_TestCase
{
    protected $log;

    public function testLoggerCallable()
    {
        $mongo = new LoggableMongo();

        $mongo->setLoggerCallable($loggerCallable = function() {});

        $this->assertSame($loggerCallable, $mongo->getLoggerCallable());
    }

    public function testLogDefault()
    {
        $mongo = new LoggableMongo();

        $mongo->setLogDefault($logDefault = array('connection' => 'default'));

        $this->assertSame($logDefault, $mongo->getLogDefault());
    }


    public function testLog()
    {
        $mongo = new LoggableMongo();
        $mongo->setLoggerCallable(array($this, 'log'));

        $mongo->log($log = array('foo' => 'bar'));

        $this->assertSame($log, $this->log);
    }

    public function testLogWithLogDefault()
    {
        $mongo = new LoggableMongo();
        $mongo->setLoggerCallable(array($this, 'log'));
        $mongo->setLogDefault($logDefault = array('connection' => 'default', 'foo' => 'foobar'));

        $mongo->log($log = array('foo' => 'bar'));

        $this->assertSame(array_merge($logDefault, $log), $this->log);
    }

    public function log(array $log)
    {
        $this->log = $log;
    }

    public function testSelectDB()
    {
        $mongo = new LoggableMongo();
        $db = $mongo->selectDB('mandango_logger');

        $this->assertInstanceOf('\Mandango\Logger\LoggableMongoDB', $db);
        $this->assertSame('mandango_logger', $db->__toString());
    }

    public function test__get()
    {
        $mongo = new LoggableMongo();
        $db = $mongo->mandango_logger;

        $this->assertInstanceOf('\Mandango\Logger\LoggableMongoDB', $db);
        $this->assertSame('mandango_logger', $db->__toString());
    }
}
