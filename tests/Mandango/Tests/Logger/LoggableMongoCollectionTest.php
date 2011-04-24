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

use Mandango\Tests\TestCase;
use Mandango\Logger\LoggableMongo;
use Mandango\Logger\LoggableMongoCollection;

class LoggableMongoCollectionTest extends TestCase
{
    protected $log;

    public function testConstructorAndGetDB()
    {
        $mongo = new LoggableMongo();
        $db = $mongo->selectDB('mandango_logger');

        $collection = new LoggableMongoCollection($db, 'mandango_logger_collection');

        $this->assertSame('mandango_logger_collection', $collection->getName());
        $this->assertSame($db, $collection->getDB());
    }

    public function testLog()
    {
        $mongo = new LoggableMongo();
        $mongo->setLoggerCallable(array($this, 'log'));
        $db = $mongo->selectDB('mandango_logger');
        $collection = $db->selectCollection('mandango_logger_collection');

        $collection->log($log = array('foo' => 'bar'));

        $this->assertSame(array_merge(array(
            'database'   => 'mandango_logger',
            'collection' => 'mandango_logger_collection',
        ), $log), $this->log);
    }

    public function log(array $log)
    {
        $this->log = $log;
    }

    public function testFind()
    {
        $mongo = new LoggableMongo();
        $db = $mongo->selectDB('mandango_logger');
        $collection = $db->selectCollection('mandango_logger_collection');

        $cursor = $collection->find();
        $this->assertInstanceOf('\Mandango\Logger\LoggableMongoCursor', $cursor);

        $cursor = $collection->find($query = array('foo' => 'bar'), $fields = array('foobar' => 1, 'barfoo' => 1));
        $info = $cursor->info();
        $this->assertSame($query, $info['query']);
        $this->assertSame($fields, $info['fields']);
    }
}
