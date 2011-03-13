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

class CoreRepositoryTest extends TestCase
{
    public function testSaveInsertingNotModified()
    {
        $article = \Model\Article::create();
        \Model\Article::repository()->save($article);
        $this->assertTrue($article->isNew());

        $articles = array(
            \Model\Article::create(),
            \Model\Article::create()->setTitle('foo'),
        );
        \Model\Article::repository()->save($articles);
        $this->assertTrue($articles[0]->isNew());
        $this->assertFalse($articles[1]->isNew());
    }

    public function testSaveUpdatingNotModified($value='')
    {
        $article = \Model\Article::create()->setTitle('foo')->save();
        \Model\Article::repository()->save($article);

        $articles = array(
            \Model\Article::create()->setTitle('a1')->save(),
            \Model\Article::create()->setTitle('a2')->save()->setTitle('a2u'),
        );
        \Model\Article::repository()->save($articles);
    }

    public function testSaveInsertSingleDocument()
    {
        $article = \Model\Article::create()->fromArray(array(
            'title'   => 'foo',
            'content' => 12345,
        ));

        \Model\Article::repository()->save($article);
        $this->assertSame(1, \Model\Article::collection()->count());

        $this->assertFalse($article->isNew());
        $this->assertFalse($article->isModified());
        $articleRaw = \Model\Article::collection()->findOne();
        $this->assertSame(3, count($articleRaw));
        $this->assertEquals($article->getId(), $articleRaw['_id']);
        $this->assertSame('foo', $articleRaw['title']);
        $this->assertSame('12345', $articleRaw['content']);
        $this->assertTrue(\Model\Article::repository()->getIdentityMap()->has($article->getId()));
    }

    public function testSaveInsertMultipleDocuments()
    {
        $articles = array();
        for ($i = 1; $i <= 5; $i++) {
            $articles[$i] = \Model\Article::create()->fromArray(array(
                'title'   => 'foo'.$i,
                'content' => 12345 + $i,
            ));
        }

        \Model\Article::repository()->save($articles);
        $this->assertSame(5, \Model\Article::collection()->count());

        foreach ($articles as $i => $article) {
            $this->assertFalse($article->isNew());
            $this->assertFalse($article->isModified());
            $articleRaw = \Model\Article::collection()->findOne(array('_id' => $article->getId()));
            $this->assertSame(3, count($articleRaw));
            $this->assertEquals($article->getId(), $articleRaw['_id']);
            $this->assertSame('foo'.$i, $articleRaw['title']);
            $this->assertSame(strval(12345 + $i), $articleRaw['content']);
            $this->assertTrue(\Model\Article::repository()->getIdentityMap()->has($article->getId()));
        }
    }

    public function testSaveUpdateSingleDocument()
    {
        $articles = array();
        for ($i = 1; $i <= 5; $i++) {
            $articles[$i] = \Model\Article::create()->fromArray(array(
                'title'   => 'foo'.$i,
                'content' => 12345 + $i,
            ));
        }
        \Model\Article::repository()->save($articles);

        $articles[2]->setTitle('updated!');
        \Model\Article::repository()->save($articles[2]);

        $this->assertFalse($articles[2]->isModified());
        $this->assertSame(4, \Model\Article::collection()->find(array('title' => new \MongoRegex('/^foo/')))->count());
    }

    public function testSaveUpdateMultipleDocument()
    {
        $articles = array();
        for ($i = 1; $i <= 5; $i++) {
            $articles[$i] = \Model\Article::create()->setTitle('foo'.$i);
        }
        \Model\Article::repository()->save($articles);

        $articles[2]->setTitle('updated!');
        $articles[4]->setTitle('updated!');
        \Model\Article::repository()->save(array($articles[2], $articles[4]));

        $this->assertFalse($articles[4]->isModified());
        $this->assertFalse($articles[4]->isModified());
        $this->assertSame(3, \Model\Article::collection()->find(array('title' => new \MongoRegex('/^foo/')))->count());
    }

    public function testSaveSaveReferences()
    {
        $article = \Model\Article::create()->setTitle('foo');
        $author = \Model\Author::create()->setName('bar');
        $article->setAuthor($author);
        $article->save();

        $this->assertFalse($article->isNew());
        $this->assertFalse($author->isNew());
        $this->assertSame($author->getId(), $article->getAuthorId());
    }

    public function testSaveSaveReferencesSameClass()
    {
        $messages = array();
        $messages['barbelith'] = \Model\Message::create()->setAuthor('barbelith');
        $messages['pablodip'] = \Model\Message::create()->setAuthor('pablodip')->setReplyTo($messages['barbelith']);

        \Model\Message::repository()->save($messages);

        $this->assertFalse($messages['pablodip']->isNew());
        $this->assertFalse($messages['barbelith']->isNew());
        $this->assertSame($messages['pablodip']->getReplyToId(), $messages['barbelith']->getId());
    }

    public function testSaveEventsInsert()
    {
        $documents = array(
            \Model\Events::create()->setName('foo')->setMyEventPrefix('2'),
            \Model\Events::create()->setName('bar')->setMyEventPrefix('1'),
        );
        \Model\Events::repository()->save($documents);

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
            \Model\Events::create()->setName('foo')->save()->clearEvents()->setName('bar')->setMyEventPrefix('2')->save(),
            \Model\Events::create()->setName('bar')->save()->clearEvents()->setName('foo')->setMyEventPrefix('1')->save()
        );

        \Model\Events::repository()->save($documents);

        $this->assertSame(array(
            '2PreUpdating',
            '2PostUpdating',
        ), $documents[0]->getEvents());
        $this->assertSame(array(
            '1PreUpdating',
            '1PostUpdating',
        ), $documents[1]->getEvents());
    }

    public function testDeleteSingleDocument()
    {
        $articles = array();
        for ($i = 1; $i <= 5; $i++) {
            $articles[$i] = \Model\Article::create()->setTitle('foo');
        }
        \Model\Article::repository()->save($articles);

        $id = $articles[2]->getId();
        \Model\Article::repository()->delete($articles[2]);

        $this->assertTrue($articles[2]->isNew());
        $this->assertNull(\Model\Article::collection()->findOne(array('_id' => $id)));
        $this->assertSame(4, \Model\Article::collection()->count());
        $this->assertFalse(\Model\Article::repository()->getIdentityMap()->has($id));
        foreach (array(1, 3, 4, 5) as $key) {
            $this->assertFalse($articles[$key]->isNew());
            $this->assertNotNull(\Model\Article::collection()->findOne(array('_id' => $articles[$key]->getId())));
            $this->assertTrue(\Model\Article::repository()->getIdentityMap()->has($articles[$key]->getId()));
        }
    }

    public function testDeleteMultipleDocuments()
    {
        $articles = array();
        for ($i = 1; $i <= 5; $i++) {
            $articles[$i] = \Model\Article::create()->setTitle('foo');
        }
        \Model\Article::repository()->save($articles);

        $ids = array($articles[2]->getId(), $articles[3]->getId());
        \Model\Article::repository()->delete(array($articles[2], $articles[3]));

        $this->assertTrue($articles[2]->isNew());
        $this->assertTrue($articles[3]->isNew());
        $this->assertSame(0, \Model\Article::collection()->find(array('_id' => array('$in' => $ids)))->count());
        $this->assertFalse(\Model\Article::repository()->getIdentityMap()->has($ids[0]));
        $this->assertFalse(\Model\Article::repository()->getIdentityMap()->has($ids[1]));
        foreach (array(1, 4, 5) as $key) {
            $this->assertFalse($articles[$key]->isNew());
            $this->assertNotNull(\Model\Article::collection()->findOne(array('_id' => $articles[$key]->getId())));
            $this->assertTrue(\Model\Article::repository()->getIdentityMap()->has($articles[$key]->getId()));
        }
    }

    public function testDeleteEventsSingleDocument()
    {
        $document = \Model\Events::create()->setName('foo')->save()->clearEvents()->setMyEventPrefix('ups')->setName('bar');
        $document->delete();

        $this->assertSame(array(
            'upsPreDeleting',
            'upsPostDeleting',
        ), $document->getEvents());
    }

    public function testEnsureIndexesMethod()
    {
        \Model\Article::repository()->ensureIndexes();

        $indexInfo = \Model\Article::collection()->getIndexInfo();

        // root
        $this->assertSame(array('slug' => 1), $indexInfo[1]['key']);
        $this->assertSame(true, $indexInfo[1]['unique']);
        $this->assertSame(array('author_id' => 1, 'is_active' => 1), $indexInfo[2]['key']);

        // embeddeds one
        $this->assertSame(array('source.name' => 1), $indexInfo[3]['key']);
        $this->assertSame(true, $indexInfo[3]['unique']);
        $this->assertSame(array('source.author_id' => 1, 'source.line' => 1), $indexInfo[4]['key']);

        // embeddeds one deep
        $this->assertSame(array('source.info.note' => 1), $indexInfo[5]['key']);
        $this->assertSame(true, $indexInfo[5]['unique']);
        $this->assertSame(array('source.info.name' => 1, 'source.info.line' => 1), $indexInfo[6]['key']);

        // embeddeds many
        $this->assertSame(array('comments.line' => 1), $indexInfo[7]['key']);
        $this->assertSame(true, $indexInfo[7]['unique']);
        $this->assertSame(array('comments.author_id' => 1, 'comments.note' => 1), $indexInfo[8]['key']);

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
        $this->assertSame('Model\Article', \Model\Article::repository()->getDocumentClass());
        $this->assertSame('Model\Category', \Model\Category::repository()->getDocumentClass());
    }

    public function testIsFile()
    {
        $this->assertFalse(\Model\Article::repository()->isFile());
        $this->assertTrue(\Model\Image::repository()->isFile());
    }

    public function testConnectionName()
    {
        $this->assertNull(\Model\Article::repository()->getConnectionName());
        $this->assertSame('global', \Model\ConnectionGlobal::repository()->getConnectionName());
    }

    public function testCollectionName()
    {
        $this->assertSame('articles', \Model\Article::repository()->getCollectionName());
        $this->assertSame('model_category', \Model\Category::repository()->getCollectionName());
    }
}
