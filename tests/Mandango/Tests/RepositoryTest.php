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
    public function setCollection($collection)
    {
        $this->collection = $collection;
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
        \Mandango\Container::set('default', $mandango);

        $this->assertSame($connections['local'], \Model\Article::repository()->getConnection());
        $this->assertSame($connections['global'], \Model\ConnectionGlobal::repository()->getConnection());
    }

    public function testCollection()
    {
        $mandango = new Mandango($this->metadata, $this->queryCache);
        $connection = new Connection($this->server, $this->dbName.'_collection');
        $mandango->setConnection('default', $connection);
        $mandango->setDefaultConnectionName('default');
        \Mandango\Container::set('default', $mandango);

        $collection = \Model\Article::repository()->collection();
        $this->assertEquals($connection->getMongoDB()->selectCollection('articles'), $collection);
        $this->assertSame($collection, \Model\Article::repository()->collection());
    }

    public function testCollectionGridFS()
    {
        $collection = \Model\Image::repository()->collection();
        $this->assertEquals($this->db->getGridFS('model_image'), $collection);
        $this->assertSame($collection, \Model\Image::repository()->collection());
    }

    public function testQuery()
    {
        $query = \Model\Article::repository()->query();
        $this->assertInstanceOf('Model\ArticleQuery', $query);

        $query = \Model\Author::repository()->query();
        $this->assertInstanceOf('Model\AuthorQuery', $query);

        $criteria = array('is_active' => true);
        $query = \Model\Article::repository()->query($criteria);
        $this->assertInstanceOf('Model\ArticleQuery', $query);
        $this->assertSame($criteria, $query->getCriteria());
    }

    public function testFind()
    {
        $articles = array();
        $articlesById = array();
        for ($i = 0; $i <= 10; $i++) {
            $articleSaved = \Model\Article::create()->setTitle('Article'.$i)->save();
            $articles[] = $article = \Model\Article::create()->setId($articleSaved->getId());
            $articlesById[$article->getId()->__toString()] = $article;
        }

        $repository = \Model\Article::repository();
        $identityMap = $repository->getIdentityMap();

        $identityMap->clear();
        $this->assertEquals($articles[1], $article1 = $repository->find($articles[1]->getId()));
        $this->assertEquals($articles[3], $article3 = $repository->find($articles[3]->getId()));
        $this->assertSame($article1, $repository->find($articles[1]->getId()));
        $this->assertSame($article3, $repository->find($articles[3]->getId()));

        $identityMap->clear();
        $this->assertEquals($articles[1], $article1 = $repository->find($articles[1]->getId()->__toString()));
        $this->assertEquals($articles[3], $article3 = $repository->find($articles[3]->getId()->__toString()));
        $this->assertSame($article1, $repository->find($articles[1]->getId()->__toString()));
        $this->assertSame($article3, $repository->find($articles[3]->getId()->__toString()));

        $identityMap->clear();
        $this->assertEquals(array(
            $articles[1]->getId()->__toString() => $articles[1],
            $articles[3]->getId()->__toString() => $articles[3],
            $articles[4]->getId()->__toString() => $articles[4],
        ), $articles1 = $repository->find($ids1 = array(
            $articles[1]->getId(),
            $articles[3]->getId(),
            $articles[4]->getId(),
        )));
        $this->assertSame($articles1, $repository->find($ids1));

        $this->assertEquals(array(
            $articles[1]->getId()->__toString() => $articles[1],
            $articles[4]->getId()->__toString() => $articles[4],
            $articles[7]->getId()->__toString() => $articles[7],
        ), $articles2 = $repository->find($ids2 = array(
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
