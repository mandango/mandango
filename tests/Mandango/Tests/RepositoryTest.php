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
use Mandango\ConnectionInterface;
use Mandango\Mandango;
use Mandango\Query;

class Repository extends BaseRepository
{
    protected $documentClass = 'MyDocument';
    protected $isFile = true;
    protected $connectionName = 'foo';
    protected $collectionName = 'bar';

    public function idToMongo($id)
    {
        return $id;
    }
}

class RepositoryMock extends Repository
{
    private $collectionNameMock;
    private $collection;
    private $connection;

    public function setCollectionName($collectionName)
    {
        $this->collectionNameMock = $collectionName;

        return $this;
    }

    public function getCollectionName()
    {
        return $this->collectionNameMock;
    }

    public function setCollection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function setConnection(ConnectionInterface $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    public function getConnection()
    {
        return $this->connection;
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

        $mandango = new Mandango($this->metadataFactory, $this->cache);
        $mandango->setConnections($connections);
        $mandango->setDefaultConnectionName('local');

        $this->assertSame($connections['local'], $mandango->getRepository('Model\Article')->getConnection());
        $this->assertSame($connections['global'], $mandango->getRepository('Model\ConnectionGlobal')->getConnection());
    }

    public function testCollection()
    {
        $mandango = new Mandango($this->metadataFactory, $this->cache);
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

    public function testIdsToMongo()
    {
        $ids = $this->mandango->getRepository('Model\Article')->idsToMongo(array(
            $this->generateObjectId(),
            $id1 = new \MongoId($this->generateObjectId()),
            $this->generateObjectId(),
        ));
        $this->assertSame(3, count($ids));
        $this->assertInstanceOf('MongoId', $ids[0]);
        $this->assertSame($id1, $ids[1]);
        $this->assertInstanceOf('MongoId', $ids[2]);
    }

    public function testFindByIdAndFindOneById()
    {
        $articles = array();
        $articlesById = array();
        for ($i = 0; $i <= 10; $i++) {
            $articleSaved = $this->mandango->create('Model\Article')->setTitle('Article'.$i)->save();
            $articles[] = $article = $this->mandango->create('Model\Article')->setId($articleSaved->getId())->setIsNew(false);
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

    public function testFindByIdShouldWorkWithMixedAlreadyQueriedAndNot()
    {
        $articlesWithIds = $this->createArticles(5);
        $articles = array_values($articlesWithIds);

        $repository = $this->getRepository('Model\Article');
        $repository->findById(array(
            $articles[1]->getId(),
            $articles[3]->getId(),
        ));

        $results = $repository->findById(array(
            $articles[0]->getId(),
            $articles[1]->getId(),
            $articles[2]->getId(),
            $articles[3]->getId(),
            $articles[4]->getId(),
        ));

        $this->assertEquals($articlesWithIds, $results);
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

    public function testUpdate()
    {
        $criteria = array('is_active' => false);
        $newObject = array('$set' => array('title' => 'ups'));

        $collection = $this->getMockBuilder('MongoCollection')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $collection
            ->expects($this->any())
            ->method('update')
            ->with($criteria, $newObject)
        ;

        $repository = new RepositoryMock($this->mandango);
        $repository->setCollection($collection);
        $repository->update($criteria, $newObject);
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

    public function testGroup()
    {
        $keys = array('category' => 1);
        $initial = array('items' => array());
        $reduce = 'function (obj, prev) { prev.items.push(obj.name); }';
        $options = array();

        $result = array(new \DateTime());

        $collection = $this->getMockBuilder('MongoCollection')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $collection
            ->expects($this->once())
            ->method('group')
            ->with($keys, $initial, $reduce, $options)
            ->will($this->returnValue($result))
        ;

        $repository = new RepositoryMock($this->mandango);
        $repository->setCollection($collection);
        $this->assertSame($result, $repository->group($keys, $initial, $reduce, $options));
    }

    public function testDistinct()
    {
        $field = 'fieldName';
        $query = array('foo' => 'bar');

        $return = new \ArrayObject();

        $collection = $this->createCollectionMock();
        $collection
            ->expects($this->once())
            ->method('distinct')
            ->with($field, $query)
            ->will($this->returnValue($return));

        $repository = $this->createRepositoryMock()
            ->setCollection($collection);

        $this->assertSame($return, $repository->distinct($field, $query));
    }

    public function testMapReduce()
    {
        $collectionName = 'myCollectionName';

        $map = new \MongoCode('map');
        $reduce = new \MongoCode('reduce');
        $out = array('replace' => 'replaceCollectionName');
        $query = array('foo' => 'bar');

        $expectedCommand = array(
            'mapreduce' => $collectionName,
            'map'       => $map,
            'reduce'    => $reduce,
            'out'       => $out,
            'query'     => $query,
        );

        $resultCollectionName = 'myResultCollectionName';
        $result = array('ok' => true, 'result' => $resultCollectionName);

        $cursor = new \DateTime();

        $resultCollection = $this->createCollectionMock();
        $resultCollection
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($cursor));

        $mongoDb = $this->createMongoDbMock();
        $mongoDb
            ->expects($this->once())
            ->method('command')
            ->with($expectedCommand)
            ->will($this->returnValue($result));
        $mongoDb
            ->expects($this->once())
            ->method('selectCollection')
            ->with($resultCollectionName)
            ->will($this->returnValue($resultCollection));

        $connection = $this->createConnectionMockWithMongoDb($mongoDb);
        $repository = $this->createRepositoryMock()
            ->setCollectionName($collectionName)
            ->setConnection($connection);

        $this->assertSame($cursor, $repository->mapReduce($map, $reduce, $out, $query));
    }

    public function testMapReduceWithOptions()
    {
        $collectionName = 'myCollectionName';

        $map = new \MongoCode('map');
        $reduce = new \MongoCode('reduce');
        $out = array('replace' => 'replaceCollectionName');
        $query = array('foo' => 'bar');
        $options = array('ups' => 2);

        $expectedCommand = array(
            'mapreduce' => $collectionName,
            'map'       => $map,
            'reduce'    => $reduce,
            'out'       => $out,
            'query'     => $query,
        );

        $resultCollectionName = 'myResultCollectionName';
        $result = array('ok' => true, 'result' => $resultCollectionName);

        $cursor = new \DateTime();

        $resultCollection = $this->createCollectionMock();
        $resultCollection
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($cursor));

        $mongoDb = $this->createMongoDbMock();
        $mongoDb
            ->expects($this->once())
            ->method('command')
            ->with($expectedCommand, $options)
            ->will($this->returnValue($result));
        $mongoDb
            ->expects($this->once())
            ->method('selectCollection')
            ->with($resultCollectionName)
            ->will($this->returnValue($resultCollection));

        $connection = $this->createConnectionMockWithMongoDb($mongoDb);
        $repository = $this->createRepositoryMock()
            ->setCollectionName($collectionName)
            ->setConnection($connection);

        $command = array();
        $this->assertSame($cursor, $repository->mapReduce($map, $reduce, $out, $query, $command, $options));
    }

    public function testMapReduceInline()
    {
        $collectionName = 'myCollectionName';

        $map = 'map';
        $reduce = 'reduce';
        $out = array('inline' => 1);
        $query = array();

        $expectedCommand = array(
            'mapreduce' => $collectionName,
            'map'       => new \MongoCode($map),
            'reduce'    => new \MongoCode($reduce),
            'out'       => $out,
            'query'     => $query,
        );

        $results = array(new \DateTime());
        $result = array('ok' => true, 'results' => $results);

        $mongoDb = $this->createMongoDbMock();
        $mongoDb
            ->expects($this->once())
            ->method('command')
            ->with($expectedCommand)
            ->will($this->returnValue($result));

        $connection = $this->createConnectionMockWithMongoDb($mongoDb);
        $repository = $this->createRepositoryMock()
            ->setCollectionName($collectionName)
            ->setConnection($connection);

        $this->assertSame($results, $repository->mapReduce($map, $reduce, $out, $query));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testMapReduceRuntimeExceptionOnError()
    {
        $collectionName = 'myCollectionName';

        $result = array('ok' => false, 'errmsg' => $errmsg = 'foobarbarfooups');

        $mongoDb = $this->createMongoDbMock();
        $mongoDb
            ->expects($this->once())
            ->method('command')
            ->will($this->returnValue($result));

        $connection = $this->getMock('Mandango\ConnectionInterface');
        $connection
            ->expects($this->any())
            ->method('getMongoDB')
            ->will($this->returnValue($mongoDb));

        $connection = $this->createConnectionMockWithMongoDb($mongoDb);
        $repository = $this->createRepositoryMock()
            ->setCollectionName($collectionName)
            ->setConnection($connection);

        $repository->mapReduce('foo', 'bar', array('inline' => 1));
    }

    private function createMongoDbMock()
    {
        return $this->getMockBuilder('MongoDB')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function createCollectionMock()
    {
        return $this->getMockBuilder('MongoCollection')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function createConnectionMockWithMongoDb($mongoDb)
    {
        $connection = $this->getMock('Mandango\ConnectionInterface');
        $connection
            ->expects($this->any())
            ->method('getMongoDB')
            ->will($this->returnValue($mongoDb));

        return $connection;
    }

    private function createRepositoryMock()
    {
        return new RepositoryMock($this->mandango);
    }
}
