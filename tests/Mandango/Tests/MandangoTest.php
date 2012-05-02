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
        $this->assertSame($this->cache, $this->mandango->getCache());
    }

    public function testGetLoggerCallable()
    {
        $loggerCallable = function() {};
        $mandango = new Mandango($this->metadataFactory, $this->cache, $loggerCallable);
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
        $mandango = new Mandango($this->metadataFactory, $this->cache);
        $this->assertFalse($mandango->hasConnection('local'));
        $mandango->setConnection('local', $connections['local']);
        $this->assertTrue($mandango->hasConnection('local'));
        $mandango->setConnection('extra', $connections['extra']);
        $this->assertSame($connections['local'], $mandango->getConnection('local'));
        $this->assertSame($connections['extra'], $mandango->getConnection('extra'));

        // setConnections, getConnections
        $mandango = new Mandango($this->metadataFactory, $this->cache);
        $mandango->setConnection('extra', $connections['extra']);
        $mandango->setConnections($setConnections = array(
          'local'  => $connections['local'],
          'global' => $connections['global'],
        ));
        $this->assertEquals($setConnections, $mandango->getConnections());

        // removeConnection
        $mandango = new Mandango($this->metadataFactory, $this->cache);
        $mandango->setConnections($connections);
        $mandango->removeConnection('local');
        $this->assertSame(array(
          'global' => $connections['global'],
          'extra'  => $connections['extra'],
        ), $mandango->getConnections());

        // clearConnections
        $mandango = new Mandango($this->metadataFactory, $this->cache);
        $mandango->setConnections($connections);
        $mandango->clearConnections();
        $this->assertSame(array(), $mandango->getConnections());

        // defaultConnection
        $mandango = new Mandango($this->metadataFactory, $this->cache);
        $mandango->setConnections($connections);
        $mandango->setDefaultConnectionName('global');
        $this->assertSame($connections['global'], $mandango->getDefaultConnection());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetConnectionNotExists()
    {
        $mandango = new Mandango($this->metadataFactory, $this->cache);
        $mandango->getConnection('no');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveConnectionNotExists()
    {
        $mandango = new Mandango($this->metadataFactory, $this->cache);
        $mandango->removeConnection('no');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetDefaultConnectionNotDefaultConnectionName()
    {
        $mandango = new Mandango($this->metadataFactory, $this->cache);
        $mandango->getDefaultConnection();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetDefaultConnectionNotExist()
    {
        $mandango = new Mandango($this->metadataFactory, $this->cache);
        $mandango->setConnection('default', $this->connection);
        $mandango->getDefaultConnection();
    }

    public function testSetConnectionLoggerCallable()
    {
        $mandango = new Mandango($this->metadataFactory, $this->cache);
        $connection = new Connection($this->server, $this->dbName);
        $mandango->setConnection('default', $connection);
        $this->assertNull($connection->getLoggerCallable());
        $this->assertNull($connection->getLogDefault());

        $mandango = new Mandango($this->metadataFactory, $this->cache, $loggerCallable = function() {});
        $connection = new Connection($this->server, $this->dbName);
        $mandango->setConnection('default', $connection);
        $this->assertSame($loggerCallable, $connection->getLoggerCallable());
        $this->assertSame(array('connection' => 'default'), $connection->getLogDefault());
    }

    public function testDefaultConnectionName()
    {
        $mandango = new Mandango($this->metadataFactory, $this->cache);
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

        $mandango = new Mandango($metadataFactory, $this->cache);
        $mandango->getMetadata($documentClass);
    }

    public function getMetadataProvider()
    {
        return array(
            array('Model\Article'),
            array('Model\Author'),
        );
    }

    public function testCreate()
    {
        $article = $this->mandango->create('Model\Article');
        $this->assertInstanceOf('Model\Article', $article);

        $author = $this->mandango->create('Model\Author');
        $this->assertInstanceOf('Model\Author', $author);

        // defaults
        $book = $this->mandango->create('Model\Book');
        $this->assertSame('good', $book->getComment());
        $this->assertSame(true, $book->getIsHere());
    }

    public function testCreateInitializeArgs()
    {
        $author = $this->mandango->create('Model\Author');
        $initializeArgs = $this->mandango->create('Model\InitializeArgs', array($author));
        $this->assertSame($author, $initializeArgs->getAuthor());
    }

    public function testGetRepository()
    {
        $mandango = new Mandango($this->metadataFactory, $this->cache);

        $articleRepository = $mandango->getRepository('Model\Article');
        $this->assertInstanceOf('Model\ArticleRepository', $articleRepository);
        $this->assertSame($mandango, $articleRepository->getMandango());
        $this->assertSame($articleRepository, $mandango->getRepository('Model\Article'));

        $categoryRepository = $mandango->getRepository('Model\Category');
        $this->assertInstanceOf('Model\CategoryRepository', $categoryRepository);
    }

    /**
     * @dataProvider fixMissingReferencesDataProvider
     */
    public function testFixAllMissingReferences($documentsPerBatch)
    {
        $author1 = $this->mandango->create('Model\Author')->setName('foo')->save();
        $author2 = $this->mandango->create('Model\Author')->setName('foo')->save();

        $category1 = $this->mandango->create('Model\Category')->setName('foo')->save();
        $category2 = $this->mandango->create('Model\Category')->setName('foo')->save();
        $category3 = $this->mandango->create('Model\Category')->setName('foo')->save();

        $article1 = $this->createArticle()->setAuthor($author1)->save();
        $article2 = $this->createArticle()->setAuthor($author2)->save();
        $article3 = $this->createArticle()->setAuthor($author1)->save();
        $article4 = $this->createArticle()->addCategories(array($category1, $category3))->save();
        $article5 = $this->createArticle()->addCategories($category2)->save();
        $article6 = $this->createArticle()->addCategories($category1)->save();

        $this->removeFromCollection($author1);
        $this->removeFromCollection($category1);

        $this->mandango->fixAllMissingReferences($documentsPerBatch);

        $this->assertFalse($this->documentExists($article1));
        $this->assertTrue($this->documentExists($article2));
        $this->assertFalse($this->documentExists($article3));

        $article4->refresh();
        $article5->refresh();
        $article6->refresh();

        $this->assertEquals(array($category3->getId()), $article4->getCategoryIds());
        $this->assertEquals(array($category2->getId()), $article5->getCategoryIds());
        $this->assertEquals(array(), $article6->getCategoryIds());
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
