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

use Mandango\Mandango;
use Mandango\Connection;

class MandangoTest extends TestCase
{
    public function testGetUnitOfWork()
    {
        $unitOfWork = $this->mandango->getUnitOfWork();
        $this->assertInstanceOf('Mandango\UnitOfWork', $unitOfWork);
        $this->assertSame($this->mandango, $unitOfWork->getMandango());
        $this->assertSame($unitOfWork, $this->mandango->getUnitOfWork());
    }

    public function testGetMetadata()
    {
        $this->assertSame($this->metadata, $this->mandango->getMetadata());
    }

    public function testGetQueryCache()
    {
        $this->assertSame($this->queryCache, $this->mandango->getQueryCache());
    }

    public function testGetLoggerCallable()
    {
        $loggerCallable = function() {};
        $mandango = new Mandango($this->metadata, $this->queryCache, $loggerCallable);
        $this->assertSame($loggerCallable, $mandango->getLoggerCallable());
    }

    public function testConnections()
    {
        $connections = array(
            'local'  => new Connection('localhost', $this->dbName.'_local'),
            'global' => new Connection('localhost', $this->dbName.'_global'),
            'extra'  => new Connection('localhost', $this->dbName.'_extra'),
        );

        // hasConnection, setConnection, getConnection
        $mandango = new Mandango($this->metadata, $this->queryCache);
        $this->assertFalse($mandango->hasConnection('local'));
        $mandango->setConnection('local', $connections['local']);
        $this->assertTrue($mandango->hasConnection('local'));
        $mandango->setConnection('extra', $connections['extra']);
        $this->assertSame($connections['local'], $mandango->getConnection('local'));
        $this->assertSame($connections['extra'], $mandango->getConnection('extra'));

        // setConnections, getConnections
        $mandango = new Mandango($this->metadata, $this->queryCache);
        $mandango->setConnection('extra', $connections['extra']);
        $mandango->setConnections($setConnections = array(
          'local'  => $connections['local'],
          'global' => $connections['global'],
        ));
        $this->assertEquals($setConnections, $mandango->getConnections());

        // removeConnection
        $mandango = new Mandango($this->metadata, $this->queryCache);
        $mandango->setConnections($connections);
        $mandango->removeConnection('local');
        $this->assertSame(array(
          'global' => $connections['global'],
          'extra'  => $connections['extra'],
        ), $mandango->getConnections());

        // clearConnections
        $mandango = new Mandango($this->metadata, $this->queryCache);
        $mandango->setConnections($connections);
        $mandango->clearConnections();
        $this->assertSame(array(), $mandango->getConnections());

        // defaultConnection
        $mandango = new Mandango($this->metadata, $this->queryCache);
        $mandango->setConnections($connections);
        $mandango->setDefaultConnectionName('global');
        $this->assertSame($connections['global'], $mandango->getDefaultConnection());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetConnectionNotExists()
    {
        $mandango = new Mandango($this->metadata, $this->queryCache);
        $mandango->getConnection('no');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveConnectionNotExists()
    {
        $mandango = new Mandango($this->metadata, $this->queryCache);
        $mandango->removeConnection('no');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetDefaultConnectionNotDefaultConnectionName()
    {
        $mandango = new Mandango($this->metadata, $this->queryCache);
        $mandango->getDefaultConnection();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetDefaultConnectionNotExist()
    {
        $mandango = new Mandango($this->metadata, $this->queryCache);
        $mandango->setConnection('default', $this->connection);
        $mandango->getDefaultConnection();
    }

    public function testSetConnectionLoggerCallable()
    {
        $mandango = new Mandango($this->metadata, $this->queryCache);
        $connection = new Connection($this->server, $this->dbName);
        $mandango->setConnection('default', $connection);
        $this->assertNull($connection->getLoggerCallable());
        $this->assertNull($connection->getLogDefault());

        $mandango = new Mandango($this->metadata, $this->queryCache, $loggerCallable = function() {});
        $connection = new Connection($this->server, $this->dbName);
        $mandango->setConnection('default', $connection);
        $this->assertSame($loggerCallable, $connection->getLoggerCallable());
        $this->assertSame(array('connection' => 'default'), $connection->getLogDefault());
    }

    public function testDefaultConnectionName()
    {
        $mandango = new Mandango($this->metadata, $this->queryCache);
        $this->assertNull($mandango->getDefaultConnectionName());
        $mandango->setDefaultConnectionName('mandango_connection');
        $this->assertSame('mandango_connection', $mandango->getDefaultConnectionName());
    }

    public function testGetRepository()
    {
        $mandango = new Mandango($this->metadata, $this->queryCache);

        $articleRepository = $mandango->getRepository('Model\Article');
        $this->assertInstanceOf('Model\ArticleRepository', $articleRepository);
        $this->assertSame($mandango, $articleRepository->getMandango());
        $this->assertSame($articleRepository, $mandango->getRepository('Model\Article'));

        $categoryRepository = $mandango->getRepository('Model\Category');
        $this->assertInstanceOf('Model\CategoryRepository', $categoryRepository);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetRepositoryNotValidEmbeddedDocumentClass()
    {
        $this->mandango->getRepository('Model\Source');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetRepositoryNotValidOtherClass()
    {
        $this->mandango->getRepository('DateTime');
    }
}
