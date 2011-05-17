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

    public function testGetMetadataFactory()
    {
        $this->assertSame($this->metadataFactory, $this->mandango->getMetadataFactory());
    }

    public function testGetQueryCache()
    {
        $this->assertSame($this->queryCache, $this->mandango->getQueryCache());
    }

    public function testGetLoggerCallable()
    {
        $loggerCallable = function() {};
        $mandango = new Mandango($this->metadataFactory, $this->queryCache, $loggerCallable);
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
        $mandango = new Mandango($this->metadataFactory, $this->queryCache);
        $this->assertFalse($mandango->hasConnection('local'));
        $mandango->setConnection('local', $connections['local']);
        $this->assertTrue($mandango->hasConnection('local'));
        $mandango->setConnection('extra', $connections['extra']);
        $this->assertSame($connections['local'], $mandango->getConnection('local'));
        $this->assertSame($connections['extra'], $mandango->getConnection('extra'));

        // setConnections, getConnections
        $mandango = new Mandango($this->metadataFactory, $this->queryCache);
        $mandango->setConnection('extra', $connections['extra']);
        $mandango->setConnections($setConnections = array(
          'local'  => $connections['local'],
          'global' => $connections['global'],
        ));
        $this->assertEquals($setConnections, $mandango->getConnections());

        // removeConnection
        $mandango = new Mandango($this->metadataFactory, $this->queryCache);
        $mandango->setConnections($connections);
        $mandango->removeConnection('local');
        $this->assertSame(array(
          'global' => $connections['global'],
          'extra'  => $connections['extra'],
        ), $mandango->getConnections());

        // clearConnections
        $mandango = new Mandango($this->metadataFactory, $this->queryCache);
        $mandango->setConnections($connections);
        $mandango->clearConnections();
        $this->assertSame(array(), $mandango->getConnections());

        // defaultConnection
        $mandango = new Mandango($this->metadataFactory, $this->queryCache);
        $mandango->setConnections($connections);
        $mandango->setDefaultConnectionName('global');
        $this->assertSame($connections['global'], $mandango->getDefaultConnection());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetConnectionNotExists()
    {
        $mandango = new Mandango($this->metadataFactory, $this->queryCache);
        $mandango->getConnection('no');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveConnectionNotExists()
    {
        $mandango = new Mandango($this->metadataFactory, $this->queryCache);
        $mandango->removeConnection('no');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetDefaultConnectionNotDefaultConnectionName()
    {
        $mandango = new Mandango($this->metadataFactory, $this->queryCache);
        $mandango->getDefaultConnection();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetDefaultConnectionNotExist()
    {
        $mandango = new Mandango($this->metadataFactory, $this->queryCache);
        $mandango->setConnection('default', $this->connection);
        $mandango->getDefaultConnection();
    }

    public function testSetConnectionLoggerCallable()
    {
        $mandango = new Mandango($this->metadataFactory, $this->queryCache);
        $connection = new Connection($this->server, $this->dbName);
        $mandango->setConnection('default', $connection);
        $this->assertNull($connection->getLoggerCallable());
        $this->assertNull($connection->getLogDefault());

        $mandango = new Mandango($this->metadataFactory, $this->queryCache, $loggerCallable = function() {});
        $connection = new Connection($this->server, $this->dbName);
        $mandango->setConnection('default', $connection);
        $this->assertSame($loggerCallable, $connection->getLoggerCallable());
        $this->assertSame(array('connection' => 'default'), $connection->getLogDefault());
    }

    public function testDefaultConnectionName()
    {
        $mandango = new Mandango($this->metadataFactory, $this->queryCache);
        $this->assertNull($mandango->getDefaultConnectionName());
        $mandango->setDefaultConnectionName('mandango_connection');
        $this->assertSame('mandango_connection', $mandango->getDefaultConnectionName());
    }

    /**
     * @dataProvider getMetadataProvider
     */
    public function testGetMetadata($documentClass)
    {
        $metadataFactory = $this->getMock('Mandango\MetadataFactory');

        $metadataFactory
            ->expects($this->once())
            ->method('getClass')
            ->with($this->equalTo($documentClass))
        ;

        $mandango = new Mandango($metadataFactory, $this->queryCache);
        $mandango->getMetadata($documentClass);
    }

    public function getMetadataProvider()
    {
        return array(
            array('Model\Article'),
            array('Model\Author'),
        );
    }

    public function testGetRepository()
    {
        $mandango = new Mandango($this->metadataFactory, $this->queryCache);

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
