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
use Mandango\Logger\LoggableMongoCursor;

class LoggableMongoCursorTest extends TestCase
{
    protected $log;

    public function testConstructorAndGetCollection()
    {
        $mongo = new LoggableMongo();
        $db = $mongo->selectDB('mandango_logger');
        $collection = $db->selectCollection('mandango_logger_collection');

        $cursor = new LoggableMongoCursor($collection);

        $this->assertSame($collection, $cursor->getCollection());
    }

    public function testLog()
    {
        $mongo = new LoggableMongo();
        $mongo->setLoggerCallable(array($this, 'log'));
        $db = $mongo->selectDB('mandango_logger');
        $collection = $db->selectCollection('mandango_logger_collection');
        $cursor = $collection->find();

        $cursor->log($log = array('foo' => 'bar'));

        $this->assertSame(array_merge(array(
            'database'   => 'mandango_logger',
            'collection' => 'mandango_logger_collection',
        ), $log), $this->log);
    }

    public function log(array $log)
    {
        $this->log = $log;
    }
}
