<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Tests\Extension;

use Mandango\Tests\TestCase;

class CoreFieldAliasTest extends TestCase
{
    public function testDocumentSettersGetters()
    {
        $this->assertTrue(method_exists('Model\Article', 'getDatabase'));
        $this->assertTrue(method_exists('Model\Article', 'setDatabase'));
        $this->assertFalse(method_exists('Model\Article', 'getBasatos'));
        $this->assertFalse(method_exists('Model\Article', 'setBasatos'));
    }

    public function testDocumentGetterQuery()
    {
        $articleRaw = array(
            'basatos' => 123
        );
        $this->mandango->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $article = $this->mandango->create('Model\Article');
        $article->setId($articleRaw['_id']);
        $article->setIsNew(false);
        $this->assertSame('123', $article->getDatabase());
    }

    public function testDocumentGetterQueryEmbedded()
    {
        $articleRaw = array(
            'source' => array(
                'desde' => 123,
            ),
        );
        $this->mandango->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $article = $this->mandango->create('Model\Article');
        $article->setId($articleRaw['_id']);
        $article->setIsNew(false);
        $this->assertSame('123', $article->getSource()->getFrom());
    }

    public function testDocumentGetterSaveFieldQueryCache()
    {
        $articleRaw = array(
            'basatos' => '123',
        );
        $this->mandango->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $query = $this->mandango->getRepository('Model\Article')->createQuery();
        $article = $query->one();

        $this->assertNull($query->getFieldsCache());
        $article->getDatabase();
        $this->assertSame(array('basatos' => 1), $query->getFieldsCache());
    }

    public function testDocumentGetterSaveFieldQueryCacheEmbedded()
    {
        $articleRaw = array(
            'source' => array(
                'desde' => '123',
            ),
        );
        $this->mandango->getRepository('Model\Article')->getCollection()->insert($articleRaw);

        $query = $this->mandango->getRepository('Model\Article')->createQuery();
        $article = $query->one();

        $this->assertNull($query->getFieldsCache());
        $article->getSource()->getFrom();
        $this->assertSame(array('source.desde' => 1), $query->getFieldsCache());
    }

    public function testDocumentSetDocumentData()
    {
        $article = $this->mandango->create('Model\Article');
        $article->setDocumentData(array(
            'basatos' => 123,
        ));
        $this->assertSame('123', $article->getDatabase());
    }

    public function testDocumentQueryForSaveNew()
    {
        $article = $this->mandango->create('Model\Article')->setDatabase(123);
        $this->assertSame(array(
            'basatos' => '123',
        ), $article->queryForSave());
    }

    public function testDocumentQueryForSaveUpdate()
    {
        $article = $this->mandango->create('Model\Article');
        $article->setDocumentData(array(
            '_id' => new \MongoId($this->generateObjectId()),
            'basatos' => '234',
        ));

        $article->setDatabase(345);
        $this->assertSame(array(
            '$set' => array(
                'basatos' => '345',
            ),
        ), $article->queryForSave());

        $article->setDatabase(null);
        $this->assertSame(array(
            '$unset' => array(
                'basatos' => 1,
            ),
        ), $article->queryForSave());
    }

    public function testDocumentQueryForSaveEmbeddedNew()
    {
        $source = $this->mandango->create('Model\Source')->setFrom(123);
        $article = $this->mandango->create('Model\Article')->setSource($source);
        $this->assertSame(array(
            'source' => array(
                'desde' => '123',
            ),
        ), $article->queryForSave());
    }

    public function testDocumentQueryForSaveEmbeddedNotNew()
    {
        $article = $this->mandango->create('Model\Article');
        $article->setDocumentData(array(
            '_id' => new \MongoId($this->generateObjectId()),
            'source' => array(
                'desde' => '234',
            ),
        ));
        $source = $article->getSource();

        $source->setFrom(345);
        $this->assertSame(array(
            '$set' => array(
                'source.desde' => '345',
            ),
        ), $article->queryForSave());

        $source->setFrom(null);
        $this->assertSame(array(
            '$unset' => array(
                'source.desde' => 1,
            ),
        ), $article->queryForSave());
    }
}
