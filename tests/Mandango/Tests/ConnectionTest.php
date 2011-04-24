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

namespace Mandango\Tests;

use Mandango\Connection;

class ConnectionTest extends TestCase
{
    public function testConnection()
    {
        $connection = new Connection($this->server, $this->dbName);

        $mongo   = $connection->getMongo();
        $mongoDB = $connection->getMongoDB();

        $this->assertInstanceOf('\Mongo', $mongo);
        $this->assertInstanceOf('\MongoDB', $mongoDB);
        $this->assertSame($this->dbName, $mongoDB->__toString());

        $this->assertSame($mongo, $connection->getMongo());
        $this->assertSame($mongoDB, $connection->getMongoDB());
    }

    public function testGetters()
    {
        $connection = new Connection('mongodb://127.0.0.1:27017', 'databaseName', array('connect' => true));

        $this->assertSame('mongodb://127.0.0.1:27017', $connection->getServer());
        $this->assertSame('databaseName', $connection->getDbName());
        $this->assertSame(array('connect' => true), $connection->getOptions());
    }

    public function testLoggerCallable()
    {
        $connection = new Connection($this->server, $this->dbName);

        $connection->setLoggerCallable($loggerCallable = array($this, 'log'));
        $this->assertSame($loggerCallable, $connection->getLoggerCallable());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSetLoggerCallableWhenTheConnectionHasAlreadyTheMongo()
    {
        $connection = new Connection($this->server, $this->dbName);
        $connection->getMongo();

        $connection->setLoggerCallable($loggerCallable = array($this, 'log'));
    }

    public function testLogDefault()
    {
        $connection = new Connection($this->server, $this->dbName);

        $connection->setLogDefault($logDefault = array('foo' => 'bar'));
        $this->assertSame($logDefault, $connection->getLogDefault());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSetLogDefaultWhenTheConnectionHasAlreadyTheMongo()
    {
        $connection = new Connection($this->server, $this->dbName);
        $connection->getMongo();

        $connection->setLogDefault($logDefault = array('foo' => 'bar'));
    }

    public function testMandangoLoggerWithLoggerCallable()
    {
        $connection = new Connection($this->server, $this->dbName);
        $connection->setLoggerCallable($loggerCallable = array($this, 'log'));
        $connection->setLogDefault($logDefault = array('foo' => 'bar'));

        $mongo   = $connection->getMongo();
        $mongoDB = $connection->getMongoDB();

        $this->assertInstanceOf('\Mandango\Logger\LoggableMongo', $mongo);
        $this->assertInstanceOf('\Mandango\Logger\LoggableMongoDB', $mongoDB);
        $this->assertSame($loggerCallable, $mongo->getLoggerCallable());
        $this->assertSame($logDefault, $mongo->getLogDefault());
        $this->assertSame($this->dbName, $mongoDB->__toString());

        $this->assertSame($mongo, $connection->getMongo());
        $this->assertSame($mongoDB, $connection->getMongoDB());
    }

    public function log()
    {
    }
}
