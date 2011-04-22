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

namespace Mandango\Tests\Document;

use Mandango\Tests\TestCase;
use Mandango\Document\Document as BaseDocument;

class Document extends BaseDocument
{
}

class DocumentTest extends TestCase
{
    public function testSetGetId()
    {
        $document = new Document();
        $this->assertNull($document->getId());

        $id = new \MongoId('123');
        $this->assertSame($document, $document->setId($id));
        $this->assertSame($id, $document->getId());

        $this->assertSame($id, $document->getAndRemoveId());
        $this->assertNull($document->getId());
    }

    public function testQueryHashes()
    {
        $hashes = array(md5(1), md5(2), md5(3));

        $document = new Document();
        $this->assertSame(array(), $document->getQueryHashes());
        $document->addQueryHash($hashes[0]);
        $this->assertSame(array($hashes[0]), $document->getQueryHashes());
        $document->addQueryHash($hashes[1]);
        $document->addQueryHash($hashes[2]);
        $this->assertSame($hashes, $document->getQueryHashes());
        $document->removeQueryHash($hashes[1]);
        $this->assertSame(array($hashes[0], $hashes[2]), $document->getQueryHashes());
        $document->clearQueryHashes();
        $this->assertSame(array(), $document->getQueryHashes());
    }

    public function testAddFieldCache()
    {
        $query1 = \Model\Article::getRepository()->createQuery();
        $query2 = \Model\Article::getRepository()->createQuery();

        $article = new \Model\Article();
        $article->addQueryHash($query1->getHash());
        $article->addFieldCache('title');
        $this->assertSame(array('title' => 1), $query1->getFieldsCache());
        $article->addFieldCache('source.name');
        $this->assertSame(array('title' => 1, 'source.name' => 1), $query1->getFieldsCache());
        $article->addQueryHash($query2->getHash());
        $article->addFieldCache('note');
        $this->assertSame(array('title' => 1, 'source.name' => 1, 'note' => 1), $query1->getFieldsCache());
        $this->assertSame(array('note' => 1), $query2->getFieldsCache());
        $article->addFieldCache('comments.infos');
        $this->assertSame(array('title' => 1, 'source.name' => 1, 'note' => 1, 'comments.infos' => 1), $query1->getFieldsCache());
        $this->assertSame(array('note' => 1, 'comments.infos' => 1), $query2->getFieldsCache());
    }

    public function testIsnew()
    {
        $document = new Document();
        $this->assertTrue($document->isNew());

        $document->setId(new \MongoId('123'));
        $this->assertFalse($document->isNew());
    }
}
