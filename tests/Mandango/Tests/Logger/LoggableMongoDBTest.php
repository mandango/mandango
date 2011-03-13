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
use Mandango\Logger\LoggableMongoDB;

class LoggableMongoDBTest extends \PHPUnit_Framework_TestCase
{
    protected $log;

    public function testConstructorAndGetMongo()
    {
        $mongo = new LoggableMongo();

        $db = new LoggableMongoDB($mongo, 'mandango_logger');

        $this->assertSame('mandango_logger', $db->__toString());
        $this->assertSame($mongo, $db->getMongo());
    }

    public function testLog()
    {
        $mongo = new LoggableMongo();
        $mongo->setLoggerCallable(array($this, 'log'));
        $db = $mongo->selectDB('mandango_logger');

        $db->log($log = array('foo' => 'bar'));

        $this->assertSame(array_merge(array(
            'database' => 'mandango_logger'
        ), $log), $this->log);
    }

    public function log(array $log)
    {
        $this->log = $log;
    }

    public function testSelectCollection()
    {
        $mongo = new LoggableMongo();
        $db = $mongo->selectDB('mandango_logger');

        $collection = $db->selectCollection('mandango_logger_collection');

        $this->assertInstanceOf('\Mandango\Logger\LoggableMongoCollection', $collection);
        $this->assertSame('mandango_logger_collection', $collection->getName());
    }

    public function test__get()
    {
        $mongo = new LoggableMongo();
        $db = $mongo->selectDB('mandango_logger');

        $collection = $db->mandango_logger_collection;

        $this->assertInstanceOf('\Mandango\Logger\LoggableMongoCollection', $collection);
        $this->assertSame('mandango_logger_collection', $collection->getName());
    }

    public function testGetGridFS()
    {
        $mongo = new LoggableMongo();
        $db = $mongo->selectDB('mandango_logger');

        $grid = $db->getGridFS('mandango_logger_grid');

        $this->assertInstanceOf('\Mandango\Logger\LoggableMongoGridFS', $grid);
        $this->assertSame('mandango_logger_grid.files', $grid->getName());
    }
}
