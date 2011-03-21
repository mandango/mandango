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
use Mandango\Group\EmbeddedGroup;

class CoreDocumentTest extends TestCase
{
    public function testConstructorDefaultValues()
    {
        $book = new \Model\Book();
        $this->assertSame('good', $book->getComment());
        $this->assertSame(true, $book->getIsHere());
    }

    public function testFieldsSettersGetters()
    {
        $article = new \Model\Article();
        $this->assertNull($article->getTitle());
        $this->assertNull($article->getContent());
        $this->assertSame($article, $article->setTitle('foo'));
        $this->assertSame($article, $article->setContent('bar'));
        $this->assertSame($article, $article->setNote(null));
        $this->assertSame('foo', $article->getTitle());
        $this->assertSame('bar', $article->getContent());
        $this->assertNull($article->getNote());
    }

    public function testFieldsSetterQueryFieldIfItIsNotQueried()
    {
        $articleRaw = array(
            'title'   => 'foo',
            'content' => 123,
        );
        \Model\Article::collection()->insert($articleRaw);

        $article = new \Model\Article();
        $article->setId($articleRaw['_id']);
        $this->assertSame($article, $article->setTitle('foo'));
        $this->assertFalse($article->isFieldModified('title'));
        $this->assertSame($article, $article->setTitle('foo'));
        $this->assertFalse($article->isFieldModified('title'));
    }

    public function testFieldsGettersQueryValueIfItDoesNotExistInNotNewDocuments()
    {
        $articleRaw = array(
            'title'   => 'foo',
            'content' => 123,
        );
        \Model\Article::collection()->insert($articleRaw);

        $article = new \Model\Article();
        $article->setId($articleRaw['_id']);
        $this->assertSame('foo', $article->getTitle());
        $this->assertSame('123', $article->getContent());
        $this->assertNull($article->getNote());
        $this->assertNull($article->getNote());
    }

    public function testFieldGetterSaveFieldsCacheQuering()
    {
        $articleRaw = array(
            'title'   => 'foo',
            'content' => 123,
        );
        \Model\Article::collection()->insert($articleRaw);

        $query = \Model\Article::query();
        $article = $query->one();

        $this->assertNull($query->getFieldsCache());
        $article->getTitle();
        $this->assertSame(array('title' => 1), $query->getFieldsCache());
        $article->getContent();
        $this->assertSame(array('title' => 1, 'content' => 1), $query->getFieldsCache());
        $article->getNote();
        $this->assertSame(array('title' => 1, 'content' => 1, 'note' => 1), $query->getFieldsCache());
    }

    public function testReferencesOneSettersGetters()
    {
        $article = new \Model\Article();
        $this->assertNull($article->getAuthor());

        $author = new \Model\Author();
        $this->assertSame($article, $article->setAuthor($author));
        $this->assertSame($author, $article->getAuthor());
        $this->assertNull($article->getAuthorId());

        $author = new \Model\Author();
        $author->setId($id = new \MongoId('123'));
        $article->setAuthor($author);
        $this->assertSame($author, $article->getAuthor());
        $this->assertSame($id, $article->getAuthorId());

        $article->setAuthor(null);
        $this->assertNull($article->getAuthor());
        $this->assertNull($article->getAuthorId());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testReferencesOneSetterClassInvalid()
    {
        \Model\Article::create()->setAuthor(\Model\Category::create());
    }

    public function testReferencesOneGetterQuery()
    {
        $author = \Model\Author::create()->setName('foo')->save();

        $article = \Model\Article::create()->setAuthorId($author->getId());
        $this->assertSame($author, $article->getAuthor());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testReferencesOneGetterQueryNotExist()
    {
        $article = \Model\Article::create()->setAuthorId(new \MongoId('123'));
        $article->getAuthor();
    }

    public function testReferencesManyGetter()
    {
        $article = new \Model\Article();
        $categories = $article->getCategories();
        $this->assertInstanceOf('Mandango\Group\ReferenceGroup', $categories);
        $this->assertSame($article, $categories->getParent());
        $this->assertSame('category_ids', $categories->getField());
        $this->assertSame($categories, $article->getCategories());
    }

    public function testUpdateReferenceFieldsReferencesOne()
    {
        $author = \Model\Author::create();
        $article = \Model\Article::create()->setAuthor($author);
        $author->setId(new \MongoId('123'));
        $article->updateReferenceFields();
        $this->assertSame($author->getId(), $article->getAuthorId());
    }

    public function testUpdateReferenceFieldsReferencesManyNew()
    {
        $article = new \Model\Article();
        $categories = $article->getCategories();
        $ids = array();
        for ($i = 1; $i <= 5; $i ++) {
            $categories->add(\Model\Category::create()->setId($ids[] = new \MongoId($i)));
        }
        $article->updateReferenceFields();
        $this->assertSame($ids, $article->getCategoryIds());
    }

    public function testUpdateReferenceFieldsReferencesManyNotNew()
    {
        $article = \Model\Article::create()->setDocumentData(array(
            '_id' => new \MongoId('123'),
            'category_ids' => $baseIds = array(new \MongoId('1'), new \MongoId('2'), new \MongoId('4'), new \MongoId('5')),
            'source' => array(
                'category_ids' => $sourceBaseIds = array(new \MongoId('11'), new \MongoId('12'), new \MongoId('13')),
            ),
            'comments' => array(
                array(
                    'category_ids' => $commentBaseIds = array(new \MongoId('21'), new \MongoId('22'), new \MongoId('23')),
                ),
            ),
        ));
        $categories = $article->getCategories();
        $addIds = array();
        for ($i = 1; $i <= 3; $i++) {
            $categories->add(\Model\Category::create()->setId($addIds[] = new \MongoId('10'.$i)));
        }
        $categories->remove(\Model\Category::create()->setId($baseIds[1]));
        $categories->remove(\Model\Category::create()->setId($baseIds[3]));

        $categories = $article->getSource()->getCategories();
        $sourceAddIds = array();
        for ($i = 1; $i <= 2; $i++) {
            $categories->add(\Model\Category::create()->setId($sourceAddIds[] = new \MongoId('101'.$i)));
        }
        $categories->remove(\Model\Category::create()->setId($sourceBaseIds[1]));

        $comments = $article->getComments()->saved();
        $categories = $comments[0]->getCategories();
        $commentAddIds = array();
        for ($i = 1; $i <= 3; $i++) {
            $categories->add(\Model\Category::create()->setId($commentAddIds[] = new \MongoId('102'.$i)));
        }
        $categories->remove(\Model\Category::create()->setId($commentBaseIds[1]));

        $article->updateReferenceFields();
        $this->assertSame(array(
            $baseIds[0],
            $baseIds[2],
            $addIds[0],
            $addIds[1],
            $addIds[2],
        ), $article->getCategoryIds());
        $this->assertSame(array(
            $sourceBaseIds[0],
            $sourceBaseIds[2],
            $sourceAddIds[0],
            $sourceAddIds[1],
        ), $article->getSource()->getCategoryIds());
        $this->assertSame(array(
            $commentBaseIds[0],
            $commentBaseIds[2],
            $commentAddIds[0],
            $commentAddIds[1],
            $commentAddIds[2],
        ), $comments[0]->getCategoryIds());
    }

    public function testSaveReferencesReferencesOne()
    {
        $article = \Model\Article::create();
        $author1 = \Model\Author::create()->setName('foo');
        $article->setAuthor($author1);
        $source = \Model\Source::create();
        $author2 = \Model\Author::create()->setName('bar');
        $source->setAuthor($author2);
        $article->setSource($source);

        $article->saveReferences();
        $this->assertFalse($author1->isModified());
        $this->assertFalse($author2->isModified());
    }

    public function testSaveReferencesReferencesMany()
    {
        $articleCategories = array(
            \Model\Category::create()->setName('c1')->save()->setName('c1u'),
            \Model\Category::create()->setName('c2')->save()->setName('c1u'),
            \Model\Category::create()->setName('c3'),
            \Model\Category::create()->setName('c4'),
        );

        $article = \Model\Article::create()->setDocumentData(array(
            '_id' => new \MongoId('123'),
            'category_ids' => array($articleCategories[0]->getId(), $articleCategories[1]->getId()),
        ));
        $categories = $article->getCategories();
        $categories->saved();
        $categories->add(array($articleCategories[2], $articleCategories[3]));

        $article->saveReferences();

        foreach ($articleCategories as $category) {
            $this->assertFalse($category->isModified());
        }
    }

    public function testEmbeddedsOneSettersGetters()
    {
        $article = new \Model\Article();
        $this->assertNull($article->getSource());

        $source = new \Model\Source();
        $this->assertSame($article, $article->setSource($source));
        $this->assertSame($source, $article->getSource());
        $this->assertSame(array('root' => $article, 'path' => 'source'), $source->getRootAndPath());

        $source2 = new \Model\Source();
        $article->setSource($source2);
        $this->assertSame($source2, $article->getSource());
        $this->assertSame(array('root' => $article, 'path' => 'source'), $source2->getRootAndPath());
    }

    public function testEmbeddedsOneSettersGettersDeep1()
    {
        $article = new \Model\Article();
        $source = new \Model\Source();
        $article->setSource($source);
        $info = new \Model\Info();
        $source->setInfo($info);

        $this->assertSame(array('root' => $article, 'path' => 'source'), $source->getRootAndPath());
        $this->assertSame(array('root' => $article, 'path' => 'source.info'), $info->getRootAndPath());
    }

    public function testEmbeddedsOneSettersGettersDeep2()
    {
        $info = new \Model\Info();
        $source = new \Model\Source();
        $source->setInfo($info);
        $this->assertNull($info->getRootAndPath());
        $article = new \Model\Article();
        $article->setSource($source);

        $this->assertSame(array('root' => $article, 'path' => 'source'), $source->getRootAndPath());
        $this->assertSame(array('root' => $article, 'path' => 'source.info'), $info->getRootAndPath());
    }

    public function testEmbeddedsOneGettersQueryValueIfItDoesNotExistInNotNewDocuments()
    {
        $articleRaw = array(
            'source' => array(
                'name' => 'foo',
                'text' => 234,
                'info' => array(
                    'text' => 'bar',
                    'line' => 345,
                ),
            ),
        );
        \Model\Article::collection()->insert($articleRaw);

        $article = new \Model\Article();
        $article->setId($articleRaw['_id']);

        $source = $article->getSource();
        $this->assertNotNull($source);
        $this->assertSame('foo', $source->getName());
        $this->assertSame('234', $source->getText());
        $this->assertNull($source->getNote());
        $this->assertNull($source->getLine());
        $info = $source->getInfo();
        $this->assertNotNull($info);
        $this->assertSame('bar', $info->getText());
        $this->assertSame('345', $info->getLine());
        $this->assertNull($info->getName());
        $this->assertNull($info->getNote());
    }

    public function testEmbeddedsOneGetterNotQueryValueIfItIsAChangedEmbedded()
    {
        $articleRaw = array(
            'source' => array(
                'name' => 'foo',
                'text' => 234,
                'info' => array(
                    'text' => 'bar',
                    'line' => 345,
                ),
            ),
        );
        \Model\Article::collection()->insert($articleRaw);

        $article = new \Model\Article();
        $article->setId($articleRaw['_id']);
        $source = new \Model\Source();
        $article->setSource($source);
        $this->assertNull($source->getName());
        $this->assertNull($source->getText());
        $this->assertNull($source->getInfo());

        // deep
        $article = new \Model\Article();
        $article->setId($articleRaw['_id']);
        $source = $article->getSource();
        $info = new \Model\Info();
        $source->setInfo($info);
        $this->assertNull($info->getText());
        $this->assertNull($info->getLine());
        $this->assertNull($info->getName());
    }

    public function testEmbeddedsOneGetterSaveFieldsCacheQuering()
    {
        $articleRaw = array(
            'source' => array(
                'name' => 'foo',
                'text' => 234,
                'info' => array(
                    'text' => 'bar',
                    'line' => 345,
                ),
            ),
        );
        \Model\Article::collection()->insert($articleRaw);

        $query = \Model\Article::query();
        $article = $query->one();

        $source = $article->getSource();
        $this->assertNull($query->getFieldsCache());
        $source->getName();
        $this->assertSame(array('source.name' => 1), $query->getFieldsCache());
        $source->getText();
        $this->assertSame(array('source.name' => 1, 'source.text' => 1), $query->getFieldsCache());
        $source->getNote();
        $this->assertSame(array('source.name' => 1, 'source.text' => 1, 'source.note' => 1), $query->getFieldsCache());

        $info = $source->getInfo();
        $this->assertSame(array('source.name' => 1, 'source.text' => 1, 'source.note' => 1), $query->getFieldsCache());
        $info->getName();
        $this->assertSame(array(
            'source.name' => 1,
            'source.text' => 1,
            'source.note' => 1,
            'source.info.name' => 1,
        ), $query->getFieldsCache());
        $info->getNote();
        $this->assertSame(array(
            'source.name' => 1,
            'source.text' => 1,
            'source.note' => 1,
            'source.info.name' => 1,
            'source.info.note' => 1,
        ), $query->getFieldsCache());
        $info->getLine();
        $this->assertSame(array(
            'source.name' => 1,
            'source.text' => 1,
            'source.note' => 1,
            'source.info.name' => 1,
            'source.info.note' => 1,
            'source.info.line' => 1,
        ), $query->getFieldsCache());
    }

    public function testEmbeddedsManyGetter()
    {
        $article = new \Model\Article();
        $comments = $article->getComments();
        $this->assertInstanceOf('Mandango\Group\EmbeddedGroup', $comments);
        $this->assertSame('Model\Comment', $comments->getDocumentClass());
        $this->assertSame($comments, $article->getComments());

        $comment = new \Model\Comment();
        $comments->add($comment);
        $infos = $comment->getInfos();
        $this->assertInstanceOf('Mandango\Group\EmbeddedGroup', $infos);
        $this->assertSame('Model\Info', $infos->getDocumentClass());
        $this->assertSame($infos, $comment->getInfos());
    }

    public function testRelationsOne()
    {
        $information = \Model\ArticleInformation::create()->setName('foo')->save();
        $article = \Model\Article::create()->setInformation($information)->save();
        $this->assertSame($article, $information->getArticle());

        \Model\Article::repository()->getIdentityMap()->clear();
        $this->assertEquals($article->getId(), $information->getArticle()->getId());
    }

    public function testRelationsManyOne()
    {
        $authors = array();
        for ($i = 0; $i < 10; $i++) {
            $authors[] = \Model\Author::create()->setName('Author'.$i)->save();
        }
        $articles = array();
        for ($i = 0; $i < 10; $i++) {
            $articles[] = \Model\Article::create()->setTitle('Article'.$i)->save();
        }

        $query = $authors[3]->getArticles();
        $this->assertInstanceOf('Mandango\Query', $query);
        $this->assertSame(array('author_id' => $authors[3]->getId()), $query->getCriteria());
        $this->assertSame(0, $query->count());
        $this->assertSame(0, $authors[5]->getArticles()->count());

        $articles[2]->setAuthor($authors[3])->save();
        $articles[3]->setAuthor($authors[3])->save();
        $articles[5]->setAuthor($authors[6])->save();

        $this->assertSame(array(
            $articles[2]->getId()->__toString() => $articles[2],
            $articles[3]->getId()->__toString() => $articles[3],
        ), $authors[3]->getArticles()->all());
        $this->assertSame(array(
            $articles[5]->getId()->__toString() => $articles[5],
        ), $authors[6]->getArticles()->all());
        $this->assertSame(0, $authors[5]->getArticles()->count());
    }

    public function testRelationsManyMany()
    {
        $categories = array();
        for ($i = 0; $i < 10; $i++) {
            $categories[] = \Model\Category::create()->setName('Category'.$i)->save();
        }
        $articles = array();
        for ($i = 0; $i < 10; $i++) {
            $articles[] = \Model\Article::create()->setTitle('Article'.$i)->save();
        }

        $query = $categories[3]->getArticles();
        $this->assertInstanceOf('Mandango\Query', $query);
        $this->assertSame(array('category_ids' => $categories[3]->getId()), $query->getCriteria());
        $this->assertSame(0, $query->count());
        $this->assertSame(0, $categories[5]->getArticles()->count());

        $articles[2]->getCategories()->add($categories[3]);
        $articles[2]->getCategories()->add($categories[4]);
        $articles[2]->save();
        $articles[3]->getCategories()->add($categories[3]);
        $articles[3]->save();
        $articles[5]->getCategories()->add($categories[6]);
        $articles[5]->save();

        $this->assertSame(array(
            $articles[2]->getId()->__toString() => $articles[2],
            $articles[3]->getId()->__toString() => $articles[3],
        ), $categories[3]->getArticles()->all());
        $this->assertSame(array(
            $articles[5]->getId()->__toString() => $articles[5],
        ), $categories[6]->getArticles()->all());
        $this->assertSame(0, $categories[5]->getArticles()->count());
    }

    public function testRelationsManyThrough()
    {
        $users = array();
        for ($i = 1; $i <= 10; $i++) {
            $users[$i] = $user = new \Model\User();
            $user->setUsername('user'.$i);
            $this->mandango->persist($user);
        }
        $articles = array();
        $articlesVotes = array();
        for ($i = 1; $i <= 10; $i++) {
            $articles[$i] = $article = new \Model\Article();
            $article->setTitle('article'.$i);
            $this->mandango->persist($article);

            for ($z = $i; $z <= 10; $z++) {
                $articleVote = new \Model\ArticleVote();
                $articleVote->setArticle($article);
                $articleVote->setUser($users[$z]);
                $this->mandango->persist($articleVote);

                $articlesVotes[$i][] = $articleVote->getUser();
            }
        }
        $this->mandango->flush();

        $ids = array();
        foreach ($articlesVotes[5] as $articleVote) {
            $ids[] = $articleVote->getId();
        }
        $query = \Model\ArticleVote::query(array('_id' => array('$in' => $ids)));
        $this->assertEquals($query->getCriteria(), $articles[5]->getVotesUsers()->getCriteria());
    }

    public function testSetMethod()
    {
        $article = \Model\Article::create();

        // fields
        $this->assertSame($article, $article->set('title', 'foo'));;
        $this->assertSame('foo', $article->getTitle());

        // references one
        $author = \Model\Author::create();
        $this->assertSame($article, $article->set('author', $author));
        $this->assertSame($author, $article->getAuthor());

        // embeddeds one
        $source = \Model\Source::create();
        $this->assertSame($article, $article->set('source', $source));
        $this->assertSame($source, $article->getSource());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetMethodInvalidDataName()
    {
        \Model\Article::create()->set('no', 'foo');
    }

    public function testGetMethod()
    {
        $article = \Model\Article::create();

        // fields
        $article->setTitle('bar');
        $this->assertSame('bar', $article->get('title'));

        // references one
        $author = \Model\Author::create();
        $article->setAuthor($author);
        $this->assertSame($author, $article->get('author'));

        // references many
        $this->assertSame($article->getCategories(), $article->get('categories'));

        // embeddeds one
        $source = \Model\Source::create();
        $article->setSource($source);
        $this->assertSame($source, $article->get('source'));

        // embeddeds many
        $this->assertSame($article->getComments(), $article->get('comments'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetMethodInvalidDataName()
    {
        \Model\Article::create()->get('no');
    }

    public function testFromArray()
    {
        $article = new \Model\Article();
        $this->assertSame($article, $article->fromArray(array(
            'title'     => 'foo',
            'content'   => 'bar',
            'is_active' => true,
            'source' => array(
                'name' => 'foobar',
                'text' => 'barfoo',
                'info' => array(
                    'text' => 'fooups',
                    'line' => 'upsfoo',
                ),
            ),
            'comments' => array(
                array(
                    'name' => 'foo',
                    'text' => 'bar',
                    'infos' => array(
                        array(
                            'name' => 'upsfoo',
                            'text' => 'fooups',
                        ),
                        array(
                            'name' => 'mongo',
                            'text' => 'db',
                        ),
                    ),
                ),
                array(
                    'text' => 'foobar',
                    'note' => 'barfoo',
                ),
            ),
        )));

        // fields
        $this->assertSame('foo', $article->getTitle());
        $this->assertSame('bar', $article->getContent());
        $this->assertTrue($article->getIsActive());

        // embeddeds one
        $this->assertNotNull($article->getSource());
        $source = $article->getSource();
        $this->assertSame('foobar', $source->getName());
        $this->assertSame('barfoo', $source->getText());
        $this->assertNotNull($source->getInfo());
        $info = $source->getInfo();
        $this->assertSame('fooups', $info->getText());
        $this->assertSame('upsfoo', $info->getLine());

        // embeddeds many
        $comments = $article->getComments()->saved();
        $this->assertSame(0, count($comments));
        $commentsAdd = $article->getComments()->getAdd();
        $this->assertSame('foo', $commentsAdd[0]->getName());
        $this->assertSame('bar', $commentsAdd[0]->getText());
        $infos = $commentsAdd[0]->getInfos()->saved();
        $this->assertSame(2, count($infos));
        $infosAdd = $commentsAdd[0]->getInfos()->getAdd();
    }

    public function testToArray()
    {
        $article = \Model\Article::create()
            ->setTitle('foo')
            ->setContent('bar')
            ->setNote(null)
            ->setIsActive(false)
        ;
        $this->assertSame(array(
            'title'     => 'foo',
            'content'   => 'bar',
            'is_active' => false,
        ), $article->toArray());
    }

    /*
     * Related to Mandango\Group\ReferenceGroup
     */
    public function testReferencesManyQuery()
    {
        $categories = array();
        $ids = array();
        for ($i = 1; $i <= 10; $i++) {
            $category = \Model\Category::create()->setName('Category'.$i)->save();
            if ($i % 2) {
                $categories[$category->getId()->__toString()] = $category;
                $ids[] = $category->getId();
            }
        }

        $article = \Model\Article::create()->setCategoryIds($ids);
        $this->assertSame($categories, $article->getCategories()->saved());
    }

    /*
     * Related to Mandango\Group\EmbeddedGroup
     */

    public function testEmbeddedsManyQueryValueIfItDoesNotExistInNotNewDocument()
    {
        $articleRaw = array(
            'comments' => array(
                array(
                    'name' => 'foo',
                    'text' => 'bar',
                    'infos' => array(
                        array(
                            'name' => 'foobar',
                            'line' => 'barfoo',
                        ),
                        array(
                            'text' => 'ups',
                        ),
                    ),
                ),
                array(
                    'name' => 'fooups',
                    'text' => 'upsfoo',
                ),
            ),
        );
        \Model\Article::collection()->insert($articleRaw);

        $article = new \Model\Article();
        $article->setId($articleRaw['_id']);

        $comments = $article->getComments()->saved();

        $this->assertSame(2, count($comments));
        $this->assertSame('foo', $comments[0]->getName());
        $this->assertSame('bar', $comments[0]->getText());
        $this->assertNull($comments[0]->getNote());
        $infos = $comments[0]->getInfos()->saved();
        $this->assertSame(2, count($infos));
        $this->assertSame('foobar', $infos[0]->getName());
        $this->assertNull($infos[0]->getText());
        $this->assertSame('barfoo', $infos[0]->getLine());
        $this->assertSame(array(), $comments[1]->getInfos()->saved());
    }

    public function testEmbeddedsManySaveFieldsCacheQuering()
    {
        $articleRaw = array(
            'comments' => array(
                array(
                    'name' => 'foo',
                    'text' => 'bar',
                    'infos' => array(
                        array(
                            'name' => 'foobar',
                            'line' => 'barfoo',
                        ),
                        array(
                            'text' => 'ups',
                        ),
                    ),
                ),
                array(
                    'name' => 'fooups',
                    'text' => 'upsfoo',
                ),
            ),
        );
        \Model\Article::collection()->insert($articleRaw);

        $query = \Model\Article::query();
        $article = $query->one();

        $this->assertNull($query->getFieldsCache());
        $comments = $article->getComments();
        $this->assertNull($query->getFieldsCache());
        $savedComments = $comments->saved();
        $this->assertSame(array('comments' => 1), $query->getFieldsCache());
        $savedInfos = $savedComments[0]->getInfos()->saved();
        $this->assertSame(array('comments' => 1), $query->getFieldsCache());
    }

    public function testEmbeddedsManyNoQueryNewDocument()
    {
        $this->assertSame(array(), \Model\Article::create()->getComments()->saved());
    }

    public function testEmbeddedsManyCount()
    {
        $articleRaw = array(
            'comments' => array(
                array(
                    'name' => 'foo',
                    'text' => 'bar',
                    'infos' => array(
                        array(
                            'name' => 'foobar',
                            'line' => 'barfoo',
                        ),
                        array(
                            'text' => 'ups',
                        ),
                    ),
                ),
                array(
                    'name' => 'fooups',
                    'text' => 'upsfoo',
                ),
                array(
                    'name' => 'foo3',
                ),
            ),
        );
        \Model\Article::collection()->insert($articleRaw);

        $article = new \Model\Article();
        $article->setId($articleRaw['_id']);

        $this->assertSame(3, $article->getComments()->count());
    }

    /*
     * Related to Mandango\Document\Document
     */

    public function testRepository()
    {
        $this->assertSame($this->mandango->getRepository('Model\Article'), \Model\Article::repository());
        $this->assertSame($this->mandango->getRepository('Model\Category'), \Model\Category::repository());
    }

    public function testRefresh()
    {
        $articleRaw = array(
            'title'     => 'foo',
            'content'   => 'bar',
            'is_active' => 1,
        );
        \Model\Article::collection()->insert($articleRaw);

        $article = \Model\Article::create()->setId($articleRaw['_id'])->setTitle('ups')->setNote('bump');
        $article->refresh();
        $this->assertFalse($article->isModified());
        $this->assertSame('foo', $article->getTitle());
        $this->assertSame('bar', $article->getContent());
        $this->assertNull($article->getNote());
        $this->assertTrue($article->getIsActive());
    }

    /*
     * Related to Mandango\Document\AbstractDocument
     */

    public function testMandango()
    {
        $this->assertSame($this->mandango, \Model\Article::mandango());
        $this->assertSame($this->mandango, \Model\Source::mandango());
    }

    public function testMetadata()
    {
        $this->assertSame($this->metadata->getClassInfo('Model\Article'), \Model\Article::metadata());
        $this->assertSame($this->metadata->getClassInfo('Model\Source'), \Model\Source::metadata());
    }

    public function testRootAndPath()
    {
        $article1 = new \Model\Article();
        $article2 = new \Model\Article();

        $source = new \Model\Source();
        $this->assertNull($source->getRootAndPath());
        $source->setRootAndPath($article1, 'source');
        $this->assertSame(array('root' => $article1, 'path' => 'source'), $source->getRootAndPath());
        $source->setRootAndPath($article2, 'fuente');
        $this->assertSame(array('root' => $article2, 'path' => 'fuente'), $source->getRootAndPath());
    }

    public function testRootAndPathEmbeddedsOne()
    {
        $info = new \Model\Info();
        $source = new \Model\Source();
        $source->setInfo($info);
        $article = new \Model\Article();
        $article->setSource($source);

        $this->assertSame(array('root' => $article, 'path' => 'source'), $source->getRootAndPath());
        $this->assertSame(array('root' => $article, 'path' => 'source.info'), $info->getRootAndPath());
    }

    public function testRootAndPathEmbeddedsMany()
    {
        $info = new \Model\Info();
        $comment = new \Model\Comment();
        $comment->getInfos()->add($info);
        $article = new \Model\Article();
        $article->getComments()->add($comment);

        $this->assertSame(array('root' => $article, 'path' => 'comments'), $article->getComments()->getRootAndPath());
        $rap = $comment->getRootAndPath();
        $this->assertSame($article, $rap['root']);
        $this->assertSame('comments._add0', $rap['path']);
        $rap = $info->getRootAndPath();
        $this->assertSame($article, $rap['root']);
        $this->assertSame('comments._add0.infos._add0', $rap['path']);
    }

    /*
     * setDocumentData
     */
    public function testSetDocumentData()
    {
        $article = new \Model\Article();
        $this->assertSame($article, $article->setDocumentData(array(
            '_id'         => $id = new \MongoId('123'),
            '_query_hash' => $queryHash = md5(1),
            'title'       => 'foo',
            'is_active'   => 1,
        )));

        $this->assertSame($id, $article->getId());
        $this->assertSame(array($queryHash), $article->getQueryHashes());
        $this->assertSame('foo', $article->getTitle());
        $this->assertTrue($article->getIsActive());
    }

    public function testSetDocumentDataCleaning()
    {
        $article = new \Model\Article();
        $article->setTitle('foo');
        $article->setDocumentData(array(
            '_id'   => new \MongoId('123'),
            'title' => 'foo',
        ), true);
        $this->assertFalse($article->isFieldModified('title'));
    }

    public function testSetDocumentDataDefaultValues()
    {
        $book = new \Model\Book();
        $book->setDocumentData(array(
            '_id' => new \MongoId('123'),
        ));
        $this->assertFalse($book->isFieldModified('comment'));
        $this->assertFalse($book->isFieldModified('is_here'));
    }

    public function testSetDocumentDataNullValues()
    {
        $articleRaw = array(
            'title'   => 'foo',
            'content' => 'bar',
            'line'    => 'ups',
            'source' => array(
                'name' => 'foobar',
                'note' => 'fooups',
            ),
        );
        \Model\Article::collection()->insert($articleRaw);

        $article = new \Model\Article();
        $article->setDocumentData(array(
            '_id' => $articleRaw['_id'],
            'source' => array(),
            '_fields' => array(
                'title'   => 1,
                'content' => 1,
                'source' => array(
                    'name' => 1,
                ),
            ),
        ));
        $this->assertNull($article->getTitle());
        $this->assertNull($article->getContent());
        $this->assertSame('ups', $article->getLine());
        $this->assertNull($article->getSource()->getName());
        $this->assertSame('fooups', $article->getSource()->getNote());
    }

    public function testSetDocumentDataEmbeddedsOne()
    {
        $infoData = array('name' => 234);
        $sourceData = array('name' => 123, 'info' => $infoData);
        $article = \Model\Article::create()->setDocumentData(array(
            '_query_hash' => $queryHash = md5('mongo'),
            'source'      => $sourceData,
        ));

        $source = $article->getSource();
        $this->assertEquals(\Model\Source::create()->setDocumentData($sourceData), $source);
        $this->assertSame(array('root' => $article, 'path' => 'source'), $source->getRootAndPath());
        $info = $source->getInfo();
        $this->assertEquals(\Model\Info::create()->setDocumentData($infoData), $info);
        $this->assertSame(array('root' => $article, 'path' => 'source.info'), $info->getRootAndPath());
    }

    public function testSetDocumentDataEmbeddedsMany()
    {
        $infosData = array(
            array('name' => 'foo', 'text' => 'foobar'),
            array('name' => 'bar', 'text' => 'barfoo'),
        );
        $commentsData = array(
            array('name' => 'ups', 'text' => 'upsfoo'),
            array('text' => 'mon', 'line' => 'monfoo', 'infos' => $infosData),
        );
        $article = \Model\Article::create()->setDocumentData(array(
            '_query_hash' => $queryHash = md5('mongodb'),
            'comments'    => $commentsData,
        ));

        $comments = $article->getComments();
        $this->assertEquals(new EmbeddedGroup('Model\Comment', $commentsData), $comments);
        $this->assertSame(array('root' => $article, 'path' => 'comments'), $comments->getRootAndPath());
        $savedComments = $comments->saved();
        $this->assertSame(2, count($savedComments));
        $this->assertEquals(\Model\Comment::create()->setDocumentData($commentsData[0]), $savedComments[0]);
        $this->assertEquals(\Model\Comment::create()->setDocumentData($commentsData[1]), $savedComments[1]);

        $this->assertSame(0, $savedComments[0]->getInfos()->count());
        $infos = $savedComments[1]->getInfos();
        $this->assertEquals(new EmbeddedGroup('Model\Info', $infosData), $infos);
        $this->assertSame(array('root' => $article, 'path' => 'comments.1.infos'), $infos->getRootAndPath());
        $savedInfos = $infos->saved();
        $this->assertSame(2, count($savedComments));
        $this->assertEquals(\Model\Info::create()->setDocumentData($infosData[0]), $savedInfos[0]);
        $this->assertEquals(\Model\Info::create()->setDocumentData($infosData[1]), $savedInfos[1]);
    }

    /*
     * isModified
     */
    public function testIsModifiedNewNotModified()
    {
        $article = new \Model\Article();
        $this->assertFalse($article->isModified());
    }

    public function testIsModifiedNewFieldsModified()
    {
        $article = new \Model\Article();
        $article->setLine('bar');
        $this->assertTrue($article->isModified());
    }

    public function testIsModifiedNewFieldsDefaultValues()
    {
        $book = new \Model\Book();
        $this->assertTrue($book->isModified());
    }

    public function testIsModifiedNotNewFieldsNotModified()
    {
        $article = new \Model\Article();
        $article->setDocumentData(array(
            '_id'       => new \MongoId('123'),
            'title'     => 'bar',
            'content'   => 'foo',
            'is_active' => true,
        ));
        $this->assertFalse($article->isModified());
    }

    public function testIsModifiedNotNewFieldsModified()
    {
        $article = new \Model\Article();
        $article->setDocumentData(array(
            '_id'       => new \MongoId('123'),
            'title'     => 'bar',
            'content'   => 'foo',
            'is_active' => true,
        ));
        $article->setContent('ups');
        $this->assertTrue($article->isModified());

        $article = new \Model\Article();
        $article->setDocumentData(array(
            '_id'       => new \MongoId('123'),
            'title'     => 'bar',
            'content'   => 'foo',
            'is_active' => true,
        ));
        $article->setLine('ups');
        $this->assertTrue($article->isModified());
    }

    public function testIsModifiedNotNewFieldsDefaultValues()
    {
        $book = new \Model\Book();
        $book->setDocumentData(array(
            '_id'   => new \MongoId('123'),
            'title' => 'foo',
        ));
        $this->assertFalse($book->isModified());
    }

    public function testIsModifiedNewEmbeddedsOne()
    {
        $article = \Model\Article::create();
        $source = \Model\Source::create();
        $article->setSource($source);
        $this->assertFalse($article->isModified());
        $source->setName('foo');
        $this->assertTrue($article->isModified());
        $article->setSource(null);
        $this->assertFalse($article->isModified());

        $info = \Model\Info::create();
        $source = \Model\Source::create()->setInfo($info);
        $article = \Model\Article::create()->setSource($source);
        $this->assertFalse($source->isModified());
        $this->assertFalse($article->isModified());
        $info->setName('bar');
        $this->assertTrue($source->isModified());
        $this->assertTrue($source->isModified());
    }

    public function testIsModifiedNotNewEmbeddedsOne()
    {
        $article = \Model\Article::create()->setDocumentData(array(
            '_id' => new \MongoId('123'),
            'source' => array('name' => 'foo'),
        ));
        $this->assertFalse($article->isModified());
        $source = $article->getSource();
        $source->setName('bar');
        $this->assertTrue($article->isModified());
        $source->setName('foo');
        $this->assertFalse($article->isModified());
        $article->setSource(\Model\Source::create());
        $this->assertTrue($article->isModified());
        $article->setSource(null);
        $this->assertTrue($article->isModified());

        $article = \Model\Article::create()->setDocumentData(array(
            '_id' => new \MongoId('123'),
            'source' => array('info' => array('name' => 'foo')),
        ));
        $source = $article->getSource();
        $this->assertFalse($source->isModified());
        $info = $source->getInfo();
        $info->setName('bar');
        $this->assertTrue($source->isModified());
        $this->assertTrue($article->isModified());
        $info->setName('foo');
        $this->assertFalse($info->isModified());
        $this->assertFalse($source->isModified());
        $this->assertFalse($article->isModified());
        $source->setInfo(\Model\Info::create());
        $this->assertTrue($source->isModified());
        $this->assertTrue($article->isModified());
    }

    public function testIsModifiedNewEmbeddedsMany()
    {
        $article = \Model\Article::create();
        $comments = $article->getComments();
        $this->assertFalse($article->isModified());
        $comments->remove(\Model\Comment::create()->setName('foo'));
        $this->assertFalse($article->isModified());
        $comment = \Model\Comment::create();
        $comments->add($comment);
        $this->assertFalse($article->isModified());
        $comment->setName('bar');
        $this->assertTrue($article->isModified());

        $article = \Model\Article::create();
        $comments = $article->getComments();
        $comment = \Model\Comment::create();
        $comments->add($comment);
        $infos = $comment->getInfos();
        $this->assertFalse($comment->isModified());
        $this->assertFalse($article->isModified());
        $infos->remove(\Model\Info::create()->setName('foo'));
        $this->assertFalse($comment->isModified());
        $this->assertFalse($article->isModified());
        $info = \Model\Info::create();
        $infos->add($info);
        $this->assertFalse($comment->isModified());
        $this->assertFalse($article->isModified());
        $info->setName('bar');
        $this->assertTrue($comment->isModified());
        $this->assertTrue($article->isModified());
    }

    public function testIsModifiedNotNewEmbeddedsMany()
    {
        $article = \Model\Article::create()->setDocumentData(array(
            '_id' => new \MongoId('123'),
            'comments' => array(
                array('name' => 'foo'),
                array('name' => 'bar'),
            ),
        ));
        $comments = $article->getComments();
        $this->assertFalse($article->isModified());
        $savedComments = $comments->saved();
        $this->assertFalse($article->isModified());
        // add
        $addComment = \Model\Comment::create();
        $comments->add($addComment);
        $this->assertFalse($article->isModified());
        $addComment->setName('foobar');
        $this->assertTrue($article->isModified());
        $comments->clearAdd();
        $this->assertFalse($article->isModified());
        // remove
        $comments->remove($savedComments[0]);
        $this->assertTrue($article->isModified());
        $comments->clearRemove();
        $this->assertFalse($article->isModified());
        // edit
        $savedComments[1]->setName('foobar');
        $this->assertTrue($article->isModified());
        $savedComments[1]->setName('bar');
        $this->assertFalse($article->isModified());
    }

    /*
     * clearModified
     */
    public function testClearModifiedFields()
    {
        $article = \Model\Article::create()->setTitle('foo');
        $article->clearModified();
        $this->assertSame('foo', $article->getTitle());
        $this->assertFalse($article->isModified());

        $article = \Model\Article::create()->setDocumentData(array('_id' => new \MongoId('1'), 'title' => 'foo'))->setTitle('bar');
        $article->clearModified();
        $this->assertSame('bar', $article->getTitle());
        $this->assertFalse($article->isModified());
    }

    public function testClearModifiedEmbeddedsOne()
    {
        $source = \Model\Source::create()->setName('foo');
        $article = \Model\Article::create()->setSource($source);
        $article->clearModified();
        $this->assertFalse($article->isEmbeddedOneChanged('source'));
        $this->assertSame($source, $article->getSource());
        $this->assertSame('foo', $source->getName());
        $this->assertFalse($source->isModified());
        $this->assertFalse($article->isModified());

        $article = \Model\Article::create()->setDocumentData(array('_id' => new \MongoId('1'), 'source' => array('name' => 'foo')));
        $source = $article->getSource();
        $source->setName('bar');
        $article->setSource(null);
        $article->clearModified();
        $this->assertFalse($article->isEmbeddedOneChanged('source'));
        $this->assertNull($article->getSource());
        $this->assertTrue($source->isModified());
        $this->assertFalse($article->isModified());
    }

    public function testClearModifiedEmbeddedsMany()
    {
        $article = \Model\Article::create();
        $comments = $article->getComments();
        $comment = \Model\Comment::create()->setName('foo');
        $article->clearModified();
        $this->assertSame(array(), $comments->getAdd());
        $this->assertFalse($comments->isSavedInitialized());
        $this->assertFalse($article->isModified());

        $article = \Model\Article::create()->setDocumentData(array(
            '_id' => new \MongoId('1'),
            'comments' => array(
                array('name' => 'foo'),
                array('name' => 'bar'),
            ),
        ));
        $comments = $article->getComments();
        $savedComments = $comments->saved();
        $comments->add(\Model\Comment::create()->setName('foobar'));
        $article->clearModified();
        $this->assertSame(array(), $comments->getAdd());
        $this->assertFalse($comments->isSavedInitialized());
        $this->assertFalse($article->isModified());

        $article = \Model\Article::create()->setDocumentData(array(
            '_id' => new \MongoId('1'),
            'comments' => array(
                array('name' => 'foo'),
                array('name' => 'bar'),
            ),
        ));
        $comments = $article->getComments();
        $savedComments = $comments->saved();
        $comments->remove($savedComments[0]);
        $article->clearModified();
        $this->assertSame(array(), $comments->getRemove());
        $this->assertFalse($comments->isSavedInitialized());
        $this->assertFalse($article->isModified());

        $article = \Model\Article::create()->setDocumentData(array(
            '_id' => new \MongoId('1'),
            'comments' => array(
                array('name' => 'foo'),
                array('name' => 'bar'),
            ),
        ));
        $comments = $article->getComments();
        $savedComments = $comments->saved();
        $savedComments[1]->setName('foobar');
        $article->clearModified();
        $this->assertFalse($comments->isSavedInitialized());
        $this->assertFalse($article->isModified());
    }

    /*
     * isFieldModified
     */
    public function testIsFieldModifiedWithoutValue()
    {
        $article = new \Model\Article();
        $this->assertFalse($article->isFieldModified('title'));
        $this->assertFalse($article->isFieldModified('content'));

        $source = \Model\Source::create();
        $this->assertFalse($source->isFieldModified('name'));
        $this->assertFalse($source->isFieldModified('text'));
    }

    public function testIsFieldModifiedChange()
    {
        $article = \Model\Article::create()->setTitle('foo')->setNote(null);
        $this->assertTrue($article->isFieldModified('title'));
        $this->assertFalse($article->isFieldModified('content'));
        $this->assertFalse($article->isFieldModified('note'));

        $source = \Model\Source::create()->setName('foo')->setNote(null);
        $this->assertTrue($source->isFieldModified('name'));
        $this->assertFalse($source->isFieldModified('text'));
        $this->assertFalse($source->isFieldModified('note'));
    }

    public function testIsFieldModifiedHydrate()
    {
        $article = new \Model\Article();
        $article->setDocumentData(array(
            '_id'     => new \MongoId('123'),
            'title'   => 'foo',
            'content' => 'bar',
            'note'    => 'foobar',
        ));
        $this->assertFalse($article->isFieldModified('title'));
        $this->assertFalse($article->isFieldModified('content'));

        // change other
        $article->setTitle('ups');
        $this->assertTrue($article->isFieldModified('title'));
        $this->assertFalse($article->isFieldModified('content'));

        // change same
        $article->setTitle('foo');
        $this->assertFalse($article->isFieldModified('title'));

        // change same directly
        $article->setNote('foobar');
        $this->assertFalse($article->isFieldModified('note'));
    }

    public function testIsFieldModifiedDefaultValues()
    {
        $book = new \Model\Book();
        $this->assertFalse($book->isFieldModified('title'));
        $this->assertTrue($book->isFieldModified('comment'));
        $this->assertTrue($book->isFieldModified('is_here'));
    }

    /*
     * getOriginalFieldValue
     */
    public function testGetOriginalFieldValueFieldsWithoutValue()
    {
        $article = new \Model\Article();
        $this->assertNull($article->getOriginalFieldValue('title'));
        $this->assertNull($article->getOriginalFieldValue('content'));
    }

    public function testGetOriginalFieldValueNew()
    {
        $article = new \Model\Article();
        $article->setTitle('foo');
        $this->assertNull($article->getOriginalFieldValue('title'));
        $this->assertNull($article->getOriginalFieldValue('content'));

        // again, the same original
        $article->setTitle('bar');
        $this->assertNull($article->getOriginalFieldValue('title'));
    }

    public function testGetOriginalFieldValueNotNew()
    {
        $article = new \Model\Article();
        $article->setDocumentData(array(
            '_id'     => new \MongoId('123'),
            'title'   => 'foo',
            'content' => 'bar',
        ));
        $this->assertSame('foo', $article->getOriginalFieldValue('title'));
        $this->assertSame('bar', $article->getOriginalFieldValue('content'));

        $article->setTitle('ups');
        $article->setContent('spu');
        $this->assertSame('foo', $article->getOriginalFieldValue('title'));
        $this->assertSame('bar', $article->getOriginalFieldValue('content'));

        // again, the same original
        $article->setTitle('some');
        $this->assertSame('foo', $article->getOriginalFieldValue('title'));
    }

    public function testGetOriginalFieldValueDefaultValues()
    {
        $article = new \Model\Article();
        $article->setDocumentData(array(
            'title'   => 'foo',
            'content' => 'bar',
        ));
        $this->assertSame('foo', $article->getOriginalFieldValue('title'));
        $this->assertSame('bar', $article->getOriginalFieldValue('content'));
    }

    /*
     * getFieldsModified()
     */
    public function testGetFieldsModifiedNewNotModified()
    {
        $article = new \Model\Article();
        $this->assertSame(array(), $article->getFieldsModified());
    }

    public function testGetFieldsModifiedNewModified()
    {
        $article = new \Model\Article();
        $article->setTitle('foo');
        $article->setContent('bar');
        $this->assertSame(array(
            'title'   => null,
            'content' => null,
        ), $article->getFieldsModified());
    }

    public function testGetFieldsModifiedNewDefaultValues()
    {
        $book = new \Model\Book();
        $this->assertSame(array(
            'comment' => null,
            'is_here' => null,
        ), $book->getFieldsModified());
    }

    public function testGetFieldsModifiedNotNew()
    {
        $article = new \Model\Article();
        $article->setDocumentData(array(
            '_id'       => new \MongoId('123'),
            'title'     => 'bar',
            'content'   => 'foo',
            'is_active' => true,
        ));
        $this->assertSame(array(), $article->getFieldsModified());

        $article->setTitle('ups');
        $article->setIsActive(false);
        $this->assertSame(array(
            'title'     => 'bar',
            'is_active' => true,
        ), $article->getFieldsModified());
    }

    public function testGetFieldsModifiedNotNewDefaultValues()
    {
        $book = new \Model\Book();
        $book->setDocumentData(array(
            '_id'   => new \MongoId('123'),
            'title' => 'foo',
        ));
        $this->assertSame(array(), $book->getFieldsModified());
    }

    /*
     * cleanFieldsModified
     */
    public function testCleanFieldsModified()
    {
        $article = new \Model\Article();
        $article->setId(new \MongoId('123'));
        $article->setTitle('foo');
        $article->setNote('bar');

        $article->clearFieldsModified();
        $this->assertFalse($article->isFieldModified('title'));
        $this->assertFalse($article->isFieldModified('bar'));
        $this->assertSame(array(), $article->getFieldsModified());
    }

    /*
     * isEmbeddedOneChanged
     */
    public function testIsEmbeddedOneChangedNewWithoutValue()
    {
        $article = new \Model\Article();
        $this->assertFalse($article->isEmbeddedOneChanged('source'));
        $source = new \Model\Source();
        $this->assertFalse($source->isEmbeddedOneChanged('info'));
    }

    public function testIsEmbeddedOneChangedNewChange()
    {
        $source = \Model\Source::create()->setInfo(new \Model\Info());
        $this->assertTrue($source->isEmbeddedOneChanged('info'));
        $article = \Model\Article::create()->setSource($source);
        $this->assertTrue($article->isEmbeddedOneChanged('source'));
    }

    public function testIsEmbeddedOneChangedNotNew()
    {
        $article = \Model\Article::create()->setDocumentData(array(
            '_id' => new \MongoId('123'),
            'title' => 'foo',
            'source' => array(
                'name' => 'bar',
                'info' => array(
                    'name' => 'foobar',
                ),
            ),
        ));
        $source = $article->getSource();
        $info = $source->getInfo();

        $this->assertFalse($article->isEmbeddedOneChanged('source'));
        $this->assertFalse($info->isEmbeddedOneChanged('info'));

        // change other
        $source->setInfo(new \Model\Info());
        $this->assertTrue($source->isEmbeddedOneChanged('info'));
        $this->assertFalse($article->isEmbeddedOneChanged('source'));
        $article->setSource(new \Model\Source());
        $this->assertTrue($article->isEmbeddedOneChanged('source'));

        // change same
        $article->setSource($source);
        $this->assertFalse($article->isEmbeddedOneChanged('source'));
        $this->assertTrue($source->isEmbeddedOneChanged('info'));
        $source->setInfo($info);
        $this->assertFalse($info->isEmbeddedOneChanged('info'));
    }

    /*
     * getOriginalEmbeddedOneValue
     */
    public function testGetOriginalEmbeddedOneValueWithoutValue()
    {
        $article = new \Model\Article();
        $this->assertNull($article->getOriginalEmbeddedOneValue('source'));
        $source = new \Model\Article();
        $this->assertNull($source->getOriginalEmbeddedOneValue('info'));
    }

    public function testGetOriginalEmbeddedOneValueNew()
    {
        $article = \Model\Article::create()->setSource(new \Model\Source());
        $this->assertNull($article->getOriginalEmbeddedOneValue('source'));
        $source = \Model\Source::create()->setInfo(new \Model\Info());
        $this->assertNull($source->getOriginalEmbeddedOneValue('info'));

        // again, the same original
        $article->setSource(new \Model\Source());
        $this->assertNull($article->getOriginalEmbeddedOneValue('source'));
        $source->setInfo(new \Model\Info());
        $this->assertNull($source->getOriginalEmbeddedOneValue('info'));
    }

    public function testGetOriginalEmbeddedOneChangedNotNew()
    {
        $article = \Model\Article::create()->setDocumentData(array(
            '_id' => new \MongoId('123'),
            'title' => 'foo',
            'source' => array(
                'name' => 'bar',
                'info' => array(
                    'name' => 'foobar',
                ),
            ),
        ));
        $source = $article->getSource();
        $info = $source->getInfo();

        $this->assertSame($source, $article->getOriginalEmbeddedOneValue('source'));
        $this->assertSame($info, $source->getOriginalEmbeddedOneValue('info'));

        // change
        $source->setInfo(new \Model\Info());
        $this->assertSame($info, $source->getOriginalEmbeddedOneValue('info'));
        $article->setSource(new \Model\Source());
        $this->assertSame($source, $article->getOriginalEmbeddedOneValue('source'));

        // change again, same original
        $source->setInfo(new \Model\Info());
        $this->assertSame($info, $source->getOriginalEmbeddedOneValue('info'));
        $article->setSource(new \Model\Source());
        $this->assertSame($source, $article->getOriginalEmbeddedOneValue('source'));

        // remove, same original
        $source->setInfo(null);
        $this->assertSame($info, $source->getOriginalEmbeddedOneValue('info'));
        $article->setSource(null);
        $this->assertSame($source, $article->getOriginalEmbeddedOneValue('source'));
    }

    /*
     * getEmbeddedsOneChanged()
     */
    public function testGetEmbeddedsOneChangedNewNotModified()
    {
        $article = \Model\Article::create();
        $this->assertSame(array(), $article->getEmbeddedsOneChanged());

        $source = \Model\Source::create();
        $this->assertSame(array(), $source->getEmbeddedsOneChanged());
    }

    public function testGetEmbeddedsOneChangedNewModified()
    {
        $article = \Model\Article::create()->setSource(\Model\Source::create());
        $this->assertSame(array('source' => null), $article->getEmbeddedsOneChanged());

        $source = \Model\Source::create()->setInfo(\Model\Info::create());
        $this->assertSame(array('info' => null), $source->getEmbeddedsOneChanged());
    }

    public function testGetEmbeddedsOneNotNew()
    {
        $article = \Model\Article::create()->setDocumentData(array(
            '_id' => new \MongoId('123'),
            'source' => array('name' => 'foo'),
        ));
        $this->assertSame(array(), $article->getEmbeddedsOneChanged());

        $source = $article->getSource();
        $article->setSource(\Model\Source::create());
        $this->assertSame(array('source' => $source), $article->getEmbeddedsOneChanged());

        $source = \Model\Source::create()->setDocumentData(array(
            'info' => array('name' => 'bar'),
        ));
        $this->assertSame(array(), $source->getEmbeddedsOneChanged());

        $info = $source->getInfo();
        $source->setInfo(\Model\Info::create());
        $this->assertSame(array('info' => $info), $source->getEmbeddedsOneChanged());
    }

    /*
     * clearEmbeddedsOneChanged()
     */
    public function testClearEmbeddedsOneChanged()
    {
        $info = \Model\Info::create();
        $source = \Model\Source::create()->setInfo($info);
        $article = \Model\Article::create()->setSource($source);
        $article->clearEmbeddedsOneChanged();
        $this->assertFalse($article->isEmbeddedOneChanged('source'));

        $info = \Model\Info::create();
        $source = \Model\Source::create()->setInfo($info);
        $source->clearEmbeddedsOneChanged();
        $this->assertFalse($source->isEmbeddedOneChanged('info'));
    }
}
