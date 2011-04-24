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
use Mandango\Container;
use Mandango\Connection;
use Mandango\Mandango;
use Mandango\Archive;
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
    protected $metadata;
    protected $queryCache;
    protected $mongo;
    protected $db;

    protected function setUp()
    {
        if (!static::$staticConnection) {
            static::$staticConnection = new Connection($this->server, $this->dbName);
        }
        $this->connection = static::$staticConnection;

        if (!static::$staticGlobalConnection) {
            static::$staticGlobalConnection = new Connection($this->server, $this->dbName.'_global');
        }
        $this->globalConnection = static::$staticGlobalConnection;

        if (!static::$staticMandango) {
            static::$staticMandango = new Mandango(new $this->metadataClass, new ArrayCache(), function($log) {});
            static::$staticMandango->setConnection('default', $this->connection);
            static::$staticMandango->setConnection('global', $this->globalConnection);
            static::$staticMandango->setDefaultConnectionName('default');
        }
        $this->mandango = static::$staticMandango;
        $this->unitOfWork = $this->mandango->getUnitOfWork();
        $this->metadata = $this->mandango->getMetadata();
        $this->queryCache = $this->mandango->getQueryCache();

        foreach ($this->mandango->getAllRepositories() as $repository) {
            $repository->getIdentityMap()->clear();
        }

        $this->mongo = $this->connection->getMongo();
        $this->db = $this->connection->getMongoDB();

        foreach ($this->db->listCollections() as $collection) {
            $collection->deleteIndexes();
            $collection->drop();
        }

        Container::set('default', $this->mandango);
        Container::setDefaultName('default');
    }

    protected function tearDown()
    {
        Container::clear();
        Archive::clear();
        TypeContainer::reset();
    }

    protected function createArticles($nb, $idAsKey = true)
    {
        $articles = array();
        foreach ($this->createArticlesRaw($nb) as $articleRaw) {
            $article = \Model\Article::create()->setId($articleRaw['_id']);
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
        \Model\Article::getRepository()->getCollection()->batchInsert($articles);

        return $articles;
    }
}
