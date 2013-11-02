<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
        $document = new Document($this->mandango);
        $this->assertNull($document->getId());

        $id = new \MongoId($this->generateObjectId());
        $this->assertSame($document, $document->setId($id));
        $this->assertSame($id, $document->getId());
    }

    public function testQueryHashes()
    {
        $hashes = array(md5(1), md5(2), md5(3));

        $document = new Document($this->mandango);
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
        $query1 = $this->mandango->getRepository('Model\Article')->createQuery();
        $query2 = $this->mandango->getRepository('Model\Article')->createQuery();

        $article = $this->mandango->create('Model\Article');
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

    public function testAddReferenceCache()
    {
        $query1 = $this->mandango->getRepository('Model\Article')->createQuery();
        $query2 = $this->mandango->getRepository('Model\Article')->createQuery();

        $article = $this->mandango->create('Model\Article');
        $article->addQueryHash($query1->getHash());
        $article->addReferenceCache('author');
        $this->assertSame(array('author'), $query1->getReferencesCache());
        $article->addReferenceCache('categories');
        $this->assertSame(array('author', 'categories'), $query1->getReferencesCache());
        $article->addQueryHash($query2->getHash());
        $article->addReferenceCache('note');
        $this->assertSame(array('author', 'categories', 'note'), $query1->getReferencesCache());
        $this->assertSame(array('note'), $query2->getReferencesCache());
        $article->addReferenceCache('comments');
        $this->assertSame(array('author', 'categories', 'note', 'comments'), $query1->getReferencesCache());
        $this->assertSame(array('note', 'comments'), $query2->getReferencesCache());
    }

    public function testAddReferenceCacheDouble()
    {
        $query1 = $this->mandango->getRepository('Model\Article')->createQuery();
        $query2 = $this->mandango->getRepository('Model\Article')->createQuery();

        $article = $this->mandango->create('Model\Article');
        $article->addQueryHash($query1->getHash());
        $article->addReferenceCache('author');
        $this->assertSame(array('author'), $query1->getReferencesCache());
        $article->addReferenceCache('author');
        $this->assertSame(array('author'), $query1->getReferencesCache());
        $article->addQueryHash($query2->getHash());
        $article->addReferenceCache('author');
        $this->assertSame(array('author'), $query1->getReferencesCache());
        $this->assertSame(array('author'), $query2->getReferencesCache());
        $article->addReferenceCache('author');
        $this->assertSame(array('author'), $query1->getReferencesCache());
        $this->assertSame(array('author'), $query2->getReferencesCache());
    }

    public function testIsnew()
    {
        $document = new Document($this->mandango);
        $this->assertTrue($document->isNew());

        $document->setIsNew(false);
        $this->assertFalse($document->isNew());
    }
}
