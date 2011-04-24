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
use Mandango\Logger\LoggableMongoGridFS;

class LoggableMongoGridFSTest extends TestCase
{
    protected $log;

    public function testConstructorAndGetDB()
    {
        $mongo = new LoggableMongo();
        $db = $mongo->selectDB('mandango_logger');

        $grid = new LoggableMongoGridFS($db, 'mandango_logger_grid');

        $this->assertSame('mandango_logger_grid.files', $grid->getName());
        $this->assertSame($db, $grid->getDB());
    }

    public function testLog()
    {
        $mongo = new LoggableMongo();
        $mongo->setLoggerCallable(array($this, 'log'));
        $db = $mongo->selectDB('mandango_logger');
        $grid = $db->getGridFS('mandango_logger_grid');

        $grid->log($log = array('foo' => 'bar'));

        $this->assertSame(array_merge(array(
            'database'   => 'mandango_logger',
            'collection' => 'mandango_logger_grid.files',
            'gridfs'     => 1
        ), $log), $this->log);
    }

    public function log(array $log)
    {
        $this->log = $log;
    }

    public function testFind()
    {
        $mongo = new LoggableMongo();
        $mongo->setLoggerCallable(array($this, 'log'));
        $db = $mongo->selectDB('mandango_logger');
        $grid = $db->getGridFS('mandango_logger_grid');

        $cursor = $grid->find();
        $this->assertInstanceOf('\Mandango\Logger\LoggableMongoGridFSCursor', $cursor);

        $cursor = $grid->find($query = array('foo' => 'bar'), $fields = array('foobar' => 1, 'barfoo' => 1));
        $info = $cursor->info();
        $this->assertSame($query, $info['query']);
        $this->assertSame($fields, $info['fields']);
    }
}
