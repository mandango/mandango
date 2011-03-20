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

use Mandango\Query;

class QueryTest extends TestCase
{
    protected $identityMap;
    protected $query;

    protected function setUp()
    {
        parent::setUp();

        $this->identityMap = \Model\Article::repository()->getIdentityMap();
        $this->query = new Query(\Model\Article::repository());
    }

    public function testConstructor()
    {
        $query = new Query($repository = \Model\Category::repository());
        $this->assertSame($repository, $query->getRepository());
        $hash = $query->getHash();
        $this->assertInternalType('string', $hash);
        $this->assertSame($hash, $query->getHash());
    }

    public function testFieldsCache()
    {
        $this->assertNull($this->query->getFieldsCache());

        $this->queryCache->set($this->query->getHash(), array('fields' => $fields = array('title' => 1, 'content' => 1)));
        $this->assertSame($fields, $this->query->getFieldsCache());

        $this->queryCache->remove($this->query->getHash());
        $this->assertNull($this->query->getFieldsCache());
    }

    public function testCriteria()
    {
        $query = $this->query;
        $this->assertSame(array(), $query->getCriteria());

        $criteria = array('is_active' => true);
        $this->assertSame($query, $query->criteria($criteria));
        $this->assertSame($criteria, $query->getCriteria());

        $criteria = array('title' => 'foo', 'content' => 'bar');
        $query->criteria($criteria);
        $this->assertSame($criteria, $query->getCriteria());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotArrayOrNull
     */
    public function testCriteriaNotArrayOrNull($value)
    {
        $this->query->criteria($value);
    }

    public function testFields()
    {
        $query = $this->query;
        $this->assertSame(array('_id' => 1), $query->getFields());

        $fields = array('title' => 1, 'content' => 1);
        $this->assertSame($query, $query->fields($fields));
        $this->assertSame($fields, $query->getFields());

        $fields = array('_id' => 1);
        $query->fields($fields);
        $this->assertSame($fields, $query->getFields());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotArrayOrNull
     */
    public function testFieldsNotArrayOrNull($value)
    {
        $this->query->fields($value);
    }

    public function testReferences()
    {
        $query = $this->query;
        $this->assertSame(array(), $query->getReferences());

        $references = array('user', 'author');
        $this->assertSame($query, $query->references($references));
        $this->assertSame($references, $query->getReferences());

        $include = array('post');
        $query->references($references);
        $this->assertSame($references, $query->getReferences());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotArrayOrNull
     */
    public function testReferencesNotArrayOrNull($value)
    {
        $this->query->references($value);
    }

    public function testSort()
    {
        $query = $this->query;
        $this->assertNull($query->getSort());

        $sort = array('is_active' => 1);
        $this->assertSame($query, $query->sort($sort));
        $this->assertSame($sort, $query->getSort());

        $sort = array('date' => -1, 'title' => 1);
        $query->sort($sort);
        $this->assertSame($sort, $query->getSort());

        $query->sort(null);
        $this->assertNull($query->getSort());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotArrayOrNull
     */
    public function testSortNotArrayOrNull($value)
    {
        $this->query->sort($value);
    }

    public function testLimit()
    {
        $query = $this->query;
        $this->assertNull($query->getLimit());

        $this->assertSame($query, $query->limit(10));
        $this->assertSame(10, $query->getLimit());

        $query->limit('20');
        $this->assertSame(20, $query->getLimit());

        $query->limit(null);
        $this->assertNull($query->getLimit());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotValidIntOrNull
     */
    public function testLimitNotValidIntOrNull($value)
    {
        $this->query->limit($value);
    }

    public function testSkip()
    {
        $query = $this->query;
        $this->assertNull($query->getSkip());

        $this->assertSame($query, $query->skip(15));
        $this->assertSame(15, $query->getSkip());

        $query->skip('40');
        $this->assertSame(40, $query->getSkip());

        $query->skip(null);
        $this->assertNull($query->getSkip());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotValidIntOrNull
     */
    public function testSkipNotValidIntOrNull($value)
    {
        $this->query->skip($value);
    }

    public function testBatchSize()
    {
        $query = $this->query;
        $this->assertNull($query->getBatchSize());

        $this->assertSame($query, $query->batchSize(15));
        $this->assertSame(15, $query->getBatchSize());

        $query->batchSize('40');
        $this->assertSame(40, $query->getBatchSize());

        $query->batchSize(null);
        $this->assertNull($query->getBatchSize());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotValidIntOrNull
     */
    public function testBatchSizeNotValidIntOrNull($value)
    {
        $this->query->batchSize($value);
    }

    public function testHint()
    {
        $query = $this->query;
        $this->assertNull($query->getHint());

        $hint = array('username' => 1);
        $this->assertSame($query, $query->hint($hint));
        $this->assertSame($hint, $query->getHint());

        $hint = array('username' => 1, 'date' => 1);
        $query->hint($hint);
        $this->assertSame($hint, $query->getHint());

        $query->hint(null);
        $this->assertNull($query->getHint());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotArrayOrNull
     */
    public function testHintNotArrayOrNull($value)
    {
        $this->query->hint($value);
    }

    public function testSnapshot()
    {
        $query = $this->query;
        $this->assertFalse($query->getSnapshot());

        $this->assertSame($query, $query->snapshot(true));
        $this->assertTrue($query->getSnapshot());

        $query->snapshot(false);
        $this->assertFalse($query->getSnapshot());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotBoolean
     */
    public function testSnapshotNotBoolean($value)
    {
        $this->query->snapshot($value);
    }

    public function testTimeout()
    {
        $query = $this->query;
        $this->assertNull($query->getTimeout());

        $this->assertSame($query, $query->timeout(15));
        $this->assertSame(15, $query->getTimeout());

        $query->timeout('40');
        $this->assertSame(40, $query->getTimeout());

        $query->timeout(null);
        $this->assertNull($query->getTimeout());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      providerNotValidIntOrNull
     */
    public function testTimeoutNotValidIntOrNull($value)
    {
        $this->query->timeout($value);
    }

    public function testAll()
    {
        $baseArticles = $this->createArticles(10);

        foreach ($baseArticles as $baseArticle) {
            $this->assertFalse($this->identityMap->has($baseArticle->getId()));
        }

        $articles = $this->query->all();
        $this->assertEquals($baseArticles, $articles);

        foreach ($articles as $article) {
            $this->assertTrue($this->identityMap->has($article->getId()));
            $this->assertSame(array($this->query->getHash()), $article->getQueryHashes());
        }

        $query = new Query(\Model\Article::repository());
        $articles2 = $query->all();
        foreach ($articles2 as $key => $article2) {
            $this->assertSame($article2, $articles[$key]);
            $this->assertSame(array($this->query->getHash(), $query->getHash()), $article2->getQueryHashes());
        }
    }

    public function testAllReferencesOne()
    {
        $articles = array();
        for ($i = 0; $i < 9; $i++) {
            $articles[] = \Model\Article::create()->setTitle('Article'.$i)->save();
        }
        $authors = array();
        for ($i = 0; $i < 9; $i++) {
            $authors[] = \Model\Author::create()->setName('Author'.$i)->save();
        }

        $articles[1]->setAuthor($authors[1])->save();
        $articles[3]->setAuthor($authors[3])->save();
        $articles[4]->setAuthor($authors[3])->save();
        $articles[6]->setAuthor($authors[6])->save();

        $articleIdentityMap = \Model\Article::repository()->getIdentityMap();
        $authorIdentityMap = \Model\Author::repository()->getIdentityMap();

        // without reference
        $articleIdentityMap->clear();
        $authorIdentityMap->clear();

        \Model\Article::query()->all();
        foreach ($articles as $article) {
            $this->assertTrue($articleIdentityMap->has($article->getId()));
        }
        foreach ($authors as $author) {
            $this->assertFalse($authorIdentityMap->has($author->getId()));
        }

        // with reference, finding all
        $articleIdentityMap->clear();
        $authorIdentityMap->clear();

        \Model\Article::query()->references(array('author'))->all();
        foreach ($articles as $article) {
            $this->assertTrue($articleIdentityMap->has($article->getId()));
        }
        foreach ($authors as $i => $author) {
            if (in_array($i, array(1, 3, 6))) {
                $this->assertTrue($authorIdentityMap->has($author->getId()));
            } else {
                $this->assertFalse($authorIdentityMap->has($author->getId()));
            }
        }

        // with reference, finding some
        $articleIdentityMap->clear();
        $authorIdentityMap->clear();

        \Model\Article::query(array(
            '_id' => array('$nin' => array($articles[6]->getId()))
        ))->references(array('author'))->all();
        foreach ($articles as $i => $article) {
            if (6 == $i) {
                $this->assertFalse($articleIdentityMap->has($article->getId()));
            } else {
                $this->assertTrue($articleIdentityMap->has($article->getId()));
            }
        }
        foreach ($authors as $i => $author) {
            if (in_array($i, array(1, 3))) {
                $this->assertTrue($authorIdentityMap->has($author->getId()));
            } else {
                $this->assertFalse($authorIdentityMap->has($author->getId()));
            }
        }
    }

    public function testAllReferencesMany()
    {
        $articles = array();
        for ($i = 0; $i < 9; $i++) {
            $articles[] = \Model\Article::create()->setTitle('Article'.$i)->save();
        }
        $categories = array();
        for ($i = 0; $i < 9; $i++) {
            $categories[] = \Model\Category::create()->setName('Category'.$i)->save();
        }

        $articles[1]->getCategories()->add(array($categories[1], $categories[2]));
        $articles[1]->save();
        $articles[3]->getCategories()->add(array($categories[2], $categories[3]));
        $articles[3]->save();
        $articles[5]->getCategories()->add(array($categories[5]));
        $articles[5]->save();

        $articleIdentityMap = \Model\Article::repository()->getIdentityMap();
        $categoryIdentityMap = \Model\Category::repository()->getIdentityMap();

        // without reference
        $articleIdentityMap->clear();
        $categoryIdentityMap->clear();

        \Model\Article::query()->all();
        foreach ($articles as $article) {
            $this->assertTrue($articleIdentityMap->has($article->getId()));
        }
        foreach ($categories as $category) {
            $this->assertFalse($categoryIdentityMap->has($category->getId()));
        }

        // with references, finding some
        $articleIdentityMap->clear();
        $categoryIdentityMap->clear();

        \Model\Article::query()->references(array('categories'))->all();
        foreach ($articles as $article) {
            $this->assertTrue($articleIdentityMap->has($article->getId()));
        }
        foreach ($categories as $i => $category) {
            if (in_array($i, array(1, 2, 3, 5))) {
                $this->assertTrue($categoryIdentityMap->has($category->getId()));
            } else {
                $this->assertFalse($categoryIdentityMap->has($category->getId()));
            }
        }

        // with references, finding some
        $articleIdentityMap->clear();
        $categoryIdentityMap->clear();

        \Model\Article::query(array(
            '_id' => array('$nin' => array($articles[5]->getId())),
        ))->references(array('categories'))->all();
        foreach ($articles as $i => $article) {
            if (5 == $i) {
                $this->assertFalse($articleIdentityMap->has($article->getId()));
            } else {
                $this->assertTrue($articleIdentityMap->has($article->getId()));
            }
        }
        foreach ($categories as $i => $category) {
            if (in_array($i, array(1, 2, 3))) {
                $this->assertTrue($categoryIdentityMap->has($category->getId()));
            } else {
                $this->assertFalse($categoryIdentityMap->has($category->getId()));
            }
        }
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAllReferencesNotExist()
    {
        \Model\Article::query()->references(array('no'))->all();
    }

    public function testIterator()
    {
        $articles = $this->createArticles(10);

        foreach ($articles as $article) {
            $this->assertFalse($this->identityMap->has($article->getId()));
        }

        $this->assertEquals($articles, iterator_to_array($this->query));

        foreach ($articles as $article) {
            $this->assertTrue($this->identityMap->has($article->getId()));
        }
    }

    public function testOne()
    {
        $articles = $this->createArticles(10);

        foreach ($articles as $article) {
            $this->assertFalse($this->identityMap->has($article->getId()));
        }

        $articleOne = array_shift($articles);
        $this->assertEquals($articleOne, $this->query->one());

        $this->assertTrue($this->identityMap->has($articleOne->getId()));
        foreach ($articles as $article) {
            $this->assertFalse($this->identityMap->has($article->getId()));
        }
    }

    public function testOneWithoutResults()
    {
        $this->assertNull($this->query->one());
    }

    public function testOneNotChangeQueryLimit()
    {
        $this->query->limit(10);
        $this->query->one();
        $this->assertSame(10, $this->query->getLimit());
    }

    public function testCount()
    {
        $articles = $this->createArticlesRaw(20);
        $this->assertSame(20, $this->query->count());
    }

    public function testCountableInterface()
    {
        $articles = $this->createArticlesRaw(5);
        $this->assertSame(5, count($this->query));
    }

    public function testCreateCursor()
    {
        $query = $this->query;

        $cursor = $query->createCursor();
        $this->assertInstanceOf('MongoCursor', $cursor);

        $articles = $this->createArticlesRaw(10);
        $results = iterator_to_array($cursor);
        foreach ($articles as $article) {
            $this->assertTrue(isset($results[$article['_id']->__toString()]));
        }
    }

    public function testCreateCursorPlaying()
    {
        $query = $this->query;

        $query
            ->criteria(array('is_active' => true))
            ->fields(array('title' => 1))
            ->sort(array('date' => -1))
            ->limit(10)
            ->skip(25)
            ->batchSize(5)
            ->hint(array('username' => 1))
            ->snapshot(true)
            ->timeout(100)
        ;

        $cursor = $query->createCursor();
        $this->assertInstanceOf('MongoCursor', $cursor);
    }

    public function providerNotArrayOrNull()
    {
        return array(
            array(true),
            array(1),
            array('string'),
        );
    }

    public function providerNotValidIntOrNull()
    {
        return array(
            array(true),
            array(array(1, 2)),
            array(1.1),
        );
    }

    public function providerNotBoolean()
    {
        return array(
            array(1),
            array('true'),
            array(array(true)),
        );
    }
}
