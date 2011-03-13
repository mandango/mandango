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

use Mandango\Tests\TestCase;
use Mandango\Logger\LoggableMongo;
use Mandango\Logger\LoggableMongoGridFSCursor;

class LoggableMongoGridFSCursorTest extends TestCase
{
    protected $log;

    public function testConstructorAndGetCollection()
    {
        $mongo = new LoggableMongo();
        $db = $mongo->selectDB('mandango_logger');
        $grid = $db->getGridFS('mandango_logger_grid');

        $cursor = new LoggableMongoGridFSCursor($grid);

        $this->assertSame($grid, $cursor->getGrid());
    }

    public function testLog()
    {
        $mongo = new LoggableMongo();
        $mongo->setLoggerCallable(array($this, 'log'));
        $db = $mongo->selectDB('mandango_logger');
        $grid = $db->getGridFS('mandango_logger_grid');
        $cursor = $grid->find();

        $cursor->log($log = array('foo' => 'bar'));

        $this->assertSame(array_merge(array(
            'database'   => 'mandango_logger',
            'collection' => 'mandango_logger_grid.files',
            'gridfs'     => 1,
        ), $log), $this->log);
    }

    public function log(array $log)
    {
        $this->log = $log;
    }
}
