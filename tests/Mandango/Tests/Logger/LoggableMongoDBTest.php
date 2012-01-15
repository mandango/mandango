<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Tests\Logger;

use Mandango\Logger\LoggableMongo;
use Mandango\Logger\LoggableMongoDB;

class LoggableMongoDBTest extends \PHPUnit_Framework_TestCase
{
    protected $log;

    protected function setUp()
    {
        if (!class_exists('Mongo')) {
            $this->markTestSkipped('Mongo is not available.');
        }
    }

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
