<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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

    public function testSetServer()
    {
        $connection = new Connection($this->server, $this->dbName);
        $connection->setServer($server = 'mongodb://localhost:27017');
        $this->assertSame($server, $connection->getServer());

        $connection->getMongo();
        try {
            $connection->setServer($this->server);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf('LogicException', $e);
        }
    }

    public function testSetDbName()
    {
        $connection = new Connection($this->server, $this->dbName);
        $connection->setDbName($dbName = 'mandango_testing');
        $this->assertSame($dbName, $connection->getDbName());

        $connection->getMongoDB();
        try {
            $connection->setDbName($this->dbName);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf('LogicException', $e);
        }
    }

    public function testSetOptions()
    {
        $connection = new Connection($this->server, $this->dbName);
        $connection->setOptions($options = array('connect' => true));
        $this->assertSame($options, $connection->getOptions());

        $connection->getMongo();
        try {
            $connection->setOptions(array());
            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf('LogicException', $e);
        }
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
