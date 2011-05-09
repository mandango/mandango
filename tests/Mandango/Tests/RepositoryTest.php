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

use Mandango\Repository as BaseRepository;
use Mandango\Connection;
use Mandango\Mandango;
use Mandango\Query;

class Repository extends BaseRepository
{
    protected $documentClass = 'MyDocument';
    protected $isFile = true;
    protected $connectionName = 'foo';
    protected $collectionName = 'bar';
}

class RepositoryMock extends Repository
{
    private $collection;

    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    public function getCollection()
    {
        return $this->collection;
    }
}

class RepositoryTest extends TestCase
{
    public function testConstructorGetMandango()
    {
        $repository = new Repository($this->mandango);
        $this->assertSame($this->mandango, $repository->getMandango());
    }

    public function testGetIdentityMap()
    {
        $repository = new Repository($this->mandango);
        $identityMap = $repository->getIdentityMap();
        $this->assertInstanceOf('Mandango\IdentityMap', $identityMap);
        $this->assertSame($identityMap, $repository->getIdentityMap());
    }

    public function testGetDocumentClass()
    {
        $repository = new Repository($this->mandango);
        $this->assertSame('MyDocument', $repository->getDocumentClass());
    }

    public function testIsFile()
    {
        $repository = new Repository($this->mandango);
        $this->assertTrue($repository->isFile());
    }

    public function testGetConnectionName()
    {
        $repository = new Repository($this->mandango);
        $this->assertSame('foo', $repository->getConnectionName());
    }

    public function testGetCollectionName()
    {
        $repository = new Repository($this->mandango);
        $this->assertSame('bar', $repository->getCollectionName());
    }

    public function testGetConnection()
    {
        $connections = array(
            'local'  => new Connection($this->server, $this->dbName.'_local'),
            'global' => new Connection($this->server, $this->dbName.'_global'),
        );

        $mandango = new Mandango($this->metadata, $this->queryCache);
        $mandango->setConnections($connections);
        $mandango->setDefaultConnectionName('local');

        $this->assertSame($connections['local'], $mandango->getRepository('Model\Article')->getConnection());
        $this->assertSame($connections['global'], $mandango->getRepository('Model\ConnectionGlobal')->getConnection());
    }

    public function testCollection()
    {
        $mandango = new Mandango($this->metadata, $this->queryCache);
        $connection = new Connection($this->server, $this->dbName.'_collection');
        $mandango->setConnection('default', $connection);
        $mandango->setDefaultConnectionName('default');

        $collection = $mandango->getRepository('Model\Article')->getCollection();
        $this->assertEquals($connection->getMongoDB()->selectCollection('articles'), $collection);
        $this->assertSame($collection, $mandango->getRepository('Model\Article')->getCollection());
    }

    public function testCollectionGridFS()
    {
        $collection = $this->mandango->getRepository('Model\Image')->getCollection();
        $this->assertEquals($this->db->getGridFS('model_image'), $collection);
        $this->assertSame($collection, $this->mandango->getRepository('Model\Image')->getCollection());
    }

    public function testQuery()
    {
        $query = $this->mandango->getRepository('Model\Article')->createQuery();
        $this->assertInstanceOf('Model\ArticleQuery', $query);

        $query = $this->mandango->getRepository('Model\Author')->createQuery();
        $this->assertInstanceOf('Model\AuthorQuery', $query);

        $criteria = array('is_active' => true);
        $query = $this->mandango->getRepository('Model\Article')->createQuery($criteria);
        $this->assertInstanceOf('Model\ArticleQuery', $query);
        $this->assertSame($criteria, $query->getCriteria());
    }

    public function testFindByIdAndFindOneById()
    {
        $articles = array();
        $articlesById = array();
        for ($i = 0; $i <= 10; $i++) {
            $articleSaved = $this->mandango->createDocument('Model\Article')->setTitle('Article'.$i)->save();
            $articles[] = $article = $this->mandango->createDocument('Model\Article')->setId($articleSaved->getId());
            $articlesById[$article->getId()->__toString()] = $article;
        }

        $repository = $this->mandango->getRepository('Model\Article');
        $identityMap = $repository->getIdentityMap();

        $identityMap->clear();
        $this->assertEquals($articles[1], $article1 = $repository->findOneById($articles[1]->getId()));
        $this->assertEquals($articles[3], $article3 = $repository->findOneById($articles[3]->getId()));
        $this->assertSame($article1, $repository->findOneById($articles[1]->getId()));
        $this->assertSame($article3, $repository->findOneById($articles[3]->getId()));

        $identityMap->clear();
        $this->assertEquals($articles[1], $article1 = $repository->findOneById($articles[1]->getId()->__toString()));
        $this->assertEquals($articles[3], $article3 = $repository->findOneById($articles[3]->getId()->__toString()));
        $this->assertSame($article1, $repository->findOneById($articles[1]->getId()->__toString()));
        $this->assertSame($article3, $repository->findOneById($articles[3]->getId()->__toString()));

        $identityMap->clear();
        $this->assertEquals(array(
            $articles[1]->getId()->__toString() => $articles[1],
            $articles[3]->getId()->__toString() => $articles[3],
            $articles[4]->getId()->__toString() => $articles[4],
        ), $articles1 = $repository->findById($ids1 = array(
            $articles[1]->getId(),
            $articles[3]->getId(),
            $articles[4]->getId(),
        )));
        $this->assertSame($articles1, $repository->findById($ids1));

        $this->assertEquals(array(
            $articles[1]->getId()->__toString() => $articles[1],
            $articles[4]->getId()->__toString() => $articles[4],
            $articles[7]->getId()->__toString() => $articles[7],
        ), $articles2 = $repository->findById($ids2 = array(
            $articles[1]->getId(),
            $articles[4]->getId(),
            $articles[7]->getId(),
        )));
        $this->assertSame($articles1[$articles[1]->getId()->__toString()], $articles2[$articles[1]->getId()->__toString()]);
        $this->assertSame($articles1[$articles[4]->getId()->__toString()], $articles2[$articles[4]->getId()->__toString()]);
    }

    public function testCount()
    {
        $criteria = array('is_active' => false);
        $count = 20;

        $collection = $this->getMockBuilder('MongoCollection')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $collection
            ->expects($this->any())
            ->method('count')
            ->with($criteria)
            ->will($this->returnValue($count))
        ;

        $repository = new RepositoryMock($this->mandango);
        $repository->setCollection($collection);
        $this->assertSame($count, $repository->count($criteria));
    }

    public function testRemove()
    {
        $criteria = array('is_active' => false);

        $collection = $this->getMockBuilder('MongoCollection')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $collection
            ->expects($this->any())
            ->method('remove')
            ->with($criteria)
        ;

        $repository = new RepositoryMock($this->mandango);
        $repository->setCollection($collection);
        $repository->remove($criteria);
    }
}
