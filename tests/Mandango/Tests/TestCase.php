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

use Mandango\Cache\ArrayCache;
use Mandango\Connection;
use Mandango\Mandango;
use Mandango\Archive;
use Mandango\Id\IdGeneratorContainer;
use Mandango\Type\Container as TypeContainer;

class TestCase extends \PHPUnit_Framework_TestCase
{
    static protected $staticConnection;
    static protected $staticGlobalConnection;
    static protected $staticMandango;

    protected $metadataClass = 'Model\Mapping\Metadata';
    protected $server = 'mongodb://localhost:27017';
    protected $dbName = 'mandango_tests';

    protected $connection;
    protected $globalConnection;
    protected $mandango;
    protected $unitOfWork;
    protected $metadataFactory;
    protected $cache;
    protected $mongo;
    protected $db;

    protected function setUp()
    {
        if (!class_exists('Mongo')) {
            $this->markTestSkipped('Mongo is not available.');
        }

        if (!static::$staticConnection) {
            static::$staticConnection = new Connection($this->server, $this->dbName);
        }
        $this->connection = static::$staticConnection;

        if (!static::$staticGlobalConnection) {
            static::$staticGlobalConnection = new Connection($this->server, $this->dbName.'_global');
        }
        $this->globalConnection = static::$staticGlobalConnection;

        if (!static::$staticMandango) {
            static::$staticMandango = new Mandango(new $this->metadataClass, new ArrayCache());
            static::$staticMandango->setConnection('default', $this->connection);
            static::$staticMandango->setConnection('global', $this->globalConnection);
            static::$staticMandango->setDefaultConnectionName('default');
        }
        $this->mandango = static::$staticMandango;
        $this->unitOfWork = $this->mandango->getUnitOfWork();
        $this->unitOfWork->clear();
        $this->unitOfWork->clear();
        $this->metadataFactory = $this->mandango->getMetadataFactory();
        $this->cache = $this->mandango->getCache();

        foreach ($this->mandango->getAllRepositories() as $repository) {
            $repository->getIdentityMap()->clear();
        }

        $this->mongo = $this->connection->getMongo();
        $this->db = $this->connection->getMongoDB();

        foreach ($this->db->listCollections() as $collection) {
            $collection->deleteIndexes();
            $collection->drop();
        }
    }

    protected function tearDown()
    {
        Archive::clear();
        IdGeneratorContainer::reset();
        TypeContainer::reset();
    }

    protected function getRepository($modelClass)
    {
        return $this->mandango->getRepository($modelClass);
    }

    protected function getCollection($modelClass)
    {
        return $this->getRepository($modelClass)->getCollection();
    }

    protected function create($modelClass)
    {
        return $this->mandango->create($modelClass);
    }

    protected function createCategory($name = 'foo')
    {
        return $this->mandango->create('Model\Category')->setName($name)->save();
    }

    protected function createArticle($title = 'foo')
    {
        return $this->mandango->create('Model\Article')->setTitle($title)->save();
    }

    protected function createArticles($nb, $idAsKey = true)
    {
        $articles = array();
        foreach ($this->createArticlesRaw($nb) as $articleRaw) {
            $article = $this->mandango->create('Model\Article')->setId($articleRaw['_id'])->setIsNew(false);
            if ($idAsKey) {
                $articles[$article->getId()->__toString()] = $article;
            } else {
                $articles[] = $article;
            }
        }

        return $articles;
    }

    protected function createArticlesRaw($nb)
    {
        $articles = array();
        for ($i=0; $i < $nb; $i++) {
            $articles[] = array(
                'title'   => 'Article'.$i,
                'content' => 'Content'.$i,
            );
        }
        $this->mandango->getRepository('Model\Article')->getCollection()->batchInsert($articles);

        return $articles;
    }

    protected function removeFromCollection($document)
    {
        $document
            ->getRepository()
            ->getCollection()
            ->remove(array('_id' => $document->getId()));
    }

    protected function documentExists($document)
    {
        return (Boolean) $document
            ->getRepository()
            ->getCollection()
            ->findOne(array('_id' => $document->getId()));
    }

    public function fixMissingReferencesDataProvider()
    {
        return array(
            array(1),
            array(2),
            array(100),
        );
    }

    protected function generateObjectId()
    {
        return implode('', array_map('dechex',
            array_map(function() { return mt_rand(0, 15); }, range(1, 24))
        ));
    }
}
