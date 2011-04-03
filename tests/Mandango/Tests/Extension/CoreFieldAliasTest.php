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
        \Model\Article::collection()->insert($articleRaw);

        $article = new \Model\Article();
        $article->setId($articleRaw['_id']);
        $this->assertSame('123', $article->getDatabase());
    }

    public function testDocumentGetterQueryEmbedded()
    {
        $articleRaw = array(
            'source' => array(
                'desde' => 123,
            ),
        );
        \Model\Article::collection()->insert($articleRaw);

        $article = new \Model\Article();
        $article->setId($articleRaw['_id']);
        $this->assertSame('123', $article->getSource()->getFrom());
    }

    public function testDocumentGetterSaveFieldQueryCache()
    {
        $articleRaw = array(
            'basatos' => '123',
        );
        \Model\Article::collection()->insert($articleRaw);

        $query = \Model\Article::query();
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
        \Model\Article::collection()->insert($articleRaw);

        $query = \Model\Article::query();
        $article = $query->one();

        $this->assertNull($query->getFieldsCache());
        $article->getSource()->getFrom();
        $this->assertSame(array('source.desde' => 1), $query->getFieldsCache());
    }

    public function testDocumentSetDocumentData()
    {
        $article = new \Model\Article();
        $article->setDocumentData(array(
            'basatos' => 123,
        ));
        $this->assertSame('123', $article->getDatabase());
    }

    public function testDocumentQueryForSaveNew()
    {
        $article = \Model\Article::create()->setDatabase(123);
        $this->assertSame(array(
            'basatos' => '123',
        ), $article->queryForSave());
    }

    public function testDocumentQueryForSaveUpdate()
    {
        $article = new \Model\Article();
        $article->setDocumentData(array(
            '_id' => new \MongoId('123'),
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
        $source = \Model\Source::create()->setFrom(123);
        $article = \Model\Article::create()->setSource($source);
        $this->assertSame(array(
            'source' => array(
                'desde' => '123',
            ),
        ), $article->queryForSave());
    }

    public function testDocumentQueryForSaveEmbeddedNotNew()
    {
        $article = new \Model\Article();
        $article->setDocumentData(array(
            '_id' => new \MongoId('123'),
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
