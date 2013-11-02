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

class CoreRepositoryTest extends TestCase
{
    public function testIdToMongo()
    {
        $id = $this->generateObjectId();
        $idToMongo = $this->mandango->getRepository('Model\Article')->idToMongo($id);
        $this->assertInstanceOf('MongoId', $idToMongo);
    }

    public function testSaveInsertingNotModified()
    {
        $article = $this->mandango->create('Model\Article');
        $this->mandango->getRepository('Model\Article')->save($article);
        $this->assertTrue($article->isNew());

        $articles = array(
            $this->mandango->create('Model\Article'),
            $this->mandango->create('Model\Article')->setTitle('foo'),
        );
        $this->mandango->getRepository('Model\Article')->save($articles);
        $this->assertTrue($articles[0]->isNew());
        $this->assertFalse($articles[1]->isNew());
    }

    public function testSaveUpdatingNotModified()
    {
        $article = $this->mandango->create('Model\Article')->setTitle('foo')->save();
        $this->mandango->getRepository('Model\Article')->save($article);

        $articles = array(
            $this->mandango->create('Model\Article')->setTitle('a1')->save(),
            $this->mandango->create('Model\Article')->setTitle('a2')->save()->setTitle('a2u'),
        );
        $this->mandango->getRepository('Model\Article')->save($articles);
    }

    public function testSaveInsertSingleDocument()
    {
        $article = $this->mandango->create('Model\Article')->fromArray(array(
            'title'   => 'foo',
            'content' => 12345,
        ));

        $this->mandango->getRepository('Model\Article')->save($article);
        $this->assertSame(1, $this->mandango->getRepository('Model\Article')->getCollection()->count());

        $this->assertFalse($article->isNew());
        $this->assertFalse($article->isModified());
        $articleRaw = $this->mandango->getRepository('Model\Article')->getCollection()->findOne();
        $this->assertSame(3, count($articleRaw));
        $this->assertEquals($article->getId(), $articleRaw['_id']);
        $this->assertSame('foo', $articleRaw['title']);
        $this->assertSame('12345', $articleRaw['content']);
        $this->assertTrue($this->mandango->getRepository('Model\Article')->getIdentityMap()->has($article->getId()));
    }

    public function testSaveInsertMultipleDocuments()
    {
        $articles = array();
        for ($i = 1; $i <= 5; $i++) {
            $articles[$i] = $this->mandango->create('Model\Article')->fromArray(array(
                'title'   => 'foo'.$i,
                'content' => 12345 + $i,
            ));
        }

        $this->mandango->getRepository('Model\Article')->save($articles);
        $this->assertSame(5, $this->mandango->getRepository('Model\Article')->getCollection()->count());

        foreach ($articles as $i => $article) {
            $this->assertFalse($article->isNew());
            $this->assertFalse($article->isModified());
            $articleRaw = $this->mandango->getRepository('Model\Article')->getCollection()->findOne(array('_id' => $article->getId()));
            $this->assertSame(3, count($articleRaw));
            $this->assertEquals($article->getId(), $articleRaw['_id']);
            $this->assertSame('foo'.$i, $articleRaw['title']);
            $this->assertSame(strval(12345 + $i), $articleRaw['content']);
            $this->assertTrue($this->mandango->getRepository('Model\Article')->getIdentityMap()->has($article->getId()));
        }
    }

    public function testSaveUpdateSingleDocument()
    {
        $articles = array();
        for ($i = 1; $i <= 5; $i++) {
            $articles[$i] = $this->mandango->create('Model\Article')->fromArray(array(
                'title'   => 'foo'.$i,
                'content' => 12345 + $i,
            ));
        }
        $this->mandango->getRepository('Model\Article')->save($articles);

        $articles[2]->setTitle('updated!');
        $this->mandango->getRepository('Model\Article')->save($articles[2]);

        $this->assertFalse($articles[2]->isModified());
        $this->assertSame(4, $this->mandango->getRepository('Model\Article')->getCollection()->find(array('title' => new \MongoRegex('/^foo/')))->count());
    }

    public function testSaveShouldConvertIdsToMongoWhenUpdating()
    {
        $article = $this->create('Model\Article')
            ->setTitle('foo')
            ->save();

        $id = $article->getId();
        $article
            ->setId($id->__toString())
            ->setTitle('bar')
            ->save();

        $collection = $this->getCollection('Model\Article');

        $result = $collection->findOne(array('_id' => $id));
        $expectedResult = array('_id' => $id, 'title' => 'bar');

        $this->assertEquals($expectedResult, $result);
    }

    public function testSaveUpdateMultipleDocument()
    {
        $articles = array();
        for ($i = 1; $i <= 5; $i++) {
            $articles[$i] = $this->mandango->create('Model\Article')->setTitle('foo'.$i);
        }
        $this->mandango->getRepository('Model\Article')->save($articles);

        $articles[2]->setTitle('updated!');
        $articles[4]->setTitle('updated!');
        $this->mandango->getRepository('Model\Article')->save(array($articles[2], $articles[4]));

        $this->assertFalse($articles[4]->isModified());
        $this->assertFalse($articles[4]->isModified());
        $this->assertSame(3, $this->mandango->getRepository('Model\Article')->getCollection()->find(array('title' => new \MongoRegex('/^foo/')))->count());
    }

    public function testSaveSaveReferences()
    {
        $article = $this->mandango->create('Model\Article')->setTitle('foo');
        $author = $this->mandango->create('Model\Author')->setName('bar');
        $article->setAuthor($author);
        $article->save();

        $this->assertFalse($article->isNew());
        $this->assertFalse($author->isNew());
        $this->assertSame($author->getId(), $article->getAuthorId());
    }

    public function testSaveSaveReferencesSameClass()
    {
        $messages = array();
        $messages['barbelith'] = $this->mandango->create('Model\Message')->setAuthor('barbelith');
        $messages['pablodip'] = $this->mandango->create('Model\Message')->setAuthor('pablodip')->setReplyTo($messages['barbelith']);

        $this->mandango->getRepository('Model\Message')->save($messages);

        $this->assertFalse($messages['pablodip']->isNew());
        $this->assertFalse($messages['barbelith']->isNew());
        $this->assertSame($messages['pablodip']->getReplyToId(), $messages['barbelith']->getId());
    }

    public function testSaveEventsInsert()
    {
        $documents = array(
            $this->mandango->create('Model\Events')->setName('foo')->setMyEventPrefix('2'),
            $this->mandango->create('Model\Events')->setName('bar')->setMyEventPrefix('1'),
        );
        $this->mandango->getRepository('Model\Events')->save($documents);

        $this->assertSame(array(
            '2PreInserting',
            '2PostInserting',
        ), $documents[0]->getEvents());
        $this->assertSame(array(
            '1PreInserting',
            '1PostInserting',
        ), $documents[1]->getEvents());
    }

    public function testSaveEventsUpdate()
    {
        $documents = array(
            $this->mandango->create('Model\Events')->setName('foo')->save()->clearEvents()->setName('bar')->setMyEventPrefix('2')->save(),
            $this->mandango->create('Model\Events')->setName('bar')->save()->clearEvents()->setName('foo')->setMyEventPrefix('1')->save()
        );

        $this->mandango->getRepository('Model\Events')->save($documents);

        $this->assertSame(array(
            '2PreUpdating',
            '2PostUpdating',
        ), $documents[0]->getEvents());
        $this->assertSame(array(
            '1PreUpdating',
            '1PostUpdating',
        ), $documents[1]->getEvents());
    }

    public function testSaveEventsPreUpdateProcessQueryLater()
    {
        $document = $this->mandango->create('Model\Events');
        $document->setName('foo');
        $document->save();
        $document->setName('bar');
        $document->save();

        $doc = $document->getRepository()->getCollection()->findOne();
        $this->assertSame('preUpdating', $doc['name']);
    }

    public function testSaveResetGroups()
    {
        // insert
        $article = $this->mandango->create('Model\Article')
            ->addCategories($category = $this->mandango->create('Model\Category')
                ->setName('foo')
            )
            ->save()
        ;
        $this->assertSame(0, count($article->getCategories()->getAdd()));

        // update
        $article
             ->addCategories($category = $this->mandango->create('Model\Category')
                ->setName('foo')
            )
            ->save()
        ;
        $this->assertSame(0, count($article->getCategories()->getAdd()));
    }

    public function testDeleteSingleDocument()
    {
        $articles = array();
        for ($i = 1; $i <= 5; $i++) {
            $articles[$i] = $this->mandango->create('Model\Article')->setTitle('foo');
        }
        $this->mandango->getRepository('Model\Article')->save($articles);

        $id = $articles[2]->getId();
        $this->mandango->getRepository('Model\Article')->delete($articles[2]);

        $this->assertTrue($articles[2]->isNew());
        $this->assertNull($this->mandango->getRepository('Model\Article')->getCollection()->findOne(array('_id' => $id)));
        $this->assertSame(4, $this->mandango->getRepository('Model\Article')->getCollection()->count());
        $this->assertFalse($this->mandango->getRepository('Model\Article')->getIdentityMap()->has($id));
        foreach (array(1, 3, 4, 5) as $key) {
            $this->assertFalse($articles[$key]->isNew());
            $this->assertNotNull($this->mandango->getRepository('Model\Article')->getCollection()->findOne(array('_id' => $articles[$key]->getId())));
            $this->assertTrue($this->mandango->getRepository('Model\Article')->getIdentityMap()->has($articles[$key]->getId()));
        }
    }

    public function testDeleteShouldConvertIdsToMongo()
    {
        $article = $this->create('Model\Article')
            ->setTitle('foo')
            ->save();

        $id = $article->getId();
        $article
            ->setId($id->__toString())
            ->delete();

        $collection = $this->getCollection('Model\Article');
        $result = $collection->findOne(array('_id' => $id));

        $this->assertNull($result);
    }

    public function testDeleteMultipleDocuments()
    {
        $articles = array();
        for ($i = 1; $i <= 5; $i++) {
            $articles[$i] = $this->mandango->create('Model\Article')->setTitle('foo');
        }
        $this->mandango->getRepository('Model\Article')->save($articles);

        $ids = array($articles[2]->getId(), $articles[3]->getId());
        $this->mandango->getRepository('Model\Article')->delete(array($articles[2], $articles[3]));

        $this->assertTrue($articles[2]->isNew());
        $this->assertTrue($articles[3]->isNew());
        $this->assertSame(0, $this->mandango->getRepository('Model\Article')->getCollection()->find(array('_id' => array('$in' => $ids)))->count());
        $this->assertFalse($this->mandango->getRepository('Model\Article')->getIdentityMap()->has($ids[0]));
        $this->assertFalse($this->mandango->getRepository('Model\Article')->getIdentityMap()->has($ids[1]));
        foreach (array(1, 4, 5) as $key) {
            $this->assertFalse($articles[$key]->isNew());
            $this->assertNotNull($this->mandango->getRepository('Model\Article')->getCollection()->findOne(array('_id' => $articles[$key]->getId())));
            $this->assertTrue($this->mandango->getRepository('Model\Article')->getIdentityMap()->has($articles[$key]->getId()));
        }
    }

    public function testDeleteEventsSingleDocument()
    {
        $document = $this->mandango->create('Model\Events')->setName('foo')->save()->clearEvents()->setMyEventPrefix('ups')->setName('bar');
        $document->delete();

        $this->assertSame(array(
            'upsPreDeleting',
            'upsPostDeleting',
        ), $document->getEvents());
    }

    public function testEnsureIndexesMethod()
    {
        $this->mandango->getRepository('Model\Article')->ensureIndexes();

        $indexInfo = $this->mandango->getRepository('Model\Article')->getCollection()->getIndexInfo();

        // root
        $this->assertSame(array('slug' => 1), $indexInfo[1]['key']);
        $this->assertSame(true, $indexInfo[1]['unique']);
        $this->assertSame(array('authorId' => 1, 'isActive' => 1), $indexInfo[2]['key']);

        // embeddeds one
        $this->assertSame(array('source.name' => 1), $indexInfo[3]['key']);
        $this->assertSame(true, $indexInfo[3]['unique']);
        $this->assertSame(array('source.authorId' => 1, 'source.line' => 1), $indexInfo[4]['key']);

        // embeddeds one deep
        $this->assertSame(array('source.info.note' => 1), $indexInfo[5]['key']);
        $this->assertSame(true, $indexInfo[5]['unique']);
        $this->assertSame(array('source.info.name' => 1, 'source.info.line' => 1), $indexInfo[6]['key']);

        // embeddeds many
        $this->assertSame(array('comments.line' => 1), $indexInfo[7]['key']);
        $this->assertSame(true, $indexInfo[7]['unique']);
        $this->assertSame(array('comments.authorId' => 1, 'comments.note' => 1), $indexInfo[8]['key']);

        // embeddeds many deep
        $this->assertSame(array('comments.infos.note' => 1), $indexInfo[9]['key']);
        $this->assertSame(true, $indexInfo[9]['unique']);
        $this->assertSame(array('comments.infos.name' => 1, 'comments.infos.line' => 1), $indexInfo[10]['key']);
    }

    /*
     * Related to Mandango\Repository
     */

    public function testDocumentClass()
    {
        $this->assertSame('Model\Article', $this->mandango->getRepository('Model\Article')->getDocumentClass());
        $this->assertSame('Model\Category', $this->mandango->getRepository('Model\Category')->getDocumentClass());
    }

    public function testIsFile()
    {
        $this->assertFalse($this->mandango->getRepository('Model\Article')->isFile());
        $this->assertTrue($this->mandango->getRepository('Model\Image')->isFile());
    }

    public function testConnectionName()
    {
        $this->assertNull($this->mandango->getRepository('Model\Article')->getConnectionName());
        $this->assertSame('global', $this->mandango->getRepository('Model\ConnectionGlobal')->getConnectionName());
    }

    public function testCollectionName()
    {
        $this->assertSame('articles', $this->mandango->getRepository('Model\Article')->getCollectionName());
        $this->assertSame('model_category', $this->mandango->getRepository('Model\Category')->getCollectionName());
    }
}
