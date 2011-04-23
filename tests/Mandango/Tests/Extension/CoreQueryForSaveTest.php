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

class CoreQueryForSaveTest extends TestCase
{
    public function testDocumentFieldsInsert()
    {
        $article = new \Model\Article();
        $article->setTitle('foo');
        $article->setContent('bar');
        $article->setNote(null);
        $article->setIsActive(1);

        $this->assertSame(array(
            'title'    => 'foo',
            'content'  => 'bar',
            'isActive' => true,
        ), $article->queryForSave());
    }

    public function testDocumentFieldsUpdate()
    {
        $article = new \Model\Article();
        $article->setDocumentData(array(
            '_id'      => new \MongoId('123'),
            'title'    => 'foo',
            'content'  => 'bar',
            'note'     => 'ups',
            'isActive' => false,
        ));
        $article->setTitle(234);
        $article->setContent('bar');
        $article->setLine('mmm');
        $article->setIsActive(null);
        $article->setDate(null);

        $this->assertSame(array(
            '$set' => array(
                'title' => '234',
                'line'  => 'mmm',
            ),
            '$unset' => array(
                'isActive' => 1,
            ),
        ), $article->queryForSave());
    }

    public function testDocumentReferencesOneInsert()
    {
        $article = \Model\Article::create()
            ->setAuthor(\Model\Author::create()->setId($id = new \MongoId('123')))
        ;

        $this->assertSame(array(
            'author' => $id,
        ), $article->queryForSave());
    }

    public function testDocumentReferencesOneUpdate()
    {
        $article = \Model\Article::create()->setDocumentData(array(
            '_id' => new \MongoId('123'),
            'author' => new \MongoId('234'),
        ));
        $article->setAuthor(\Model\Author::create()->setId($id = new \MongoId('345')));

        $this->assertSame(array(
            '$set' => array(
                'author' => $id,
            ),
        ), $article->queryForSave());
    }

    public function testDocumentReferencesManyInsert()
    {
        $categories = array();
        $ids = array();
        for ($i = 1; $i <= 10; $i ++) {
            $categories[] = \Model\Category::create()->setId($ids[] = new \MongoId($i));
        }

        $article = \Model\Article::create();
        $article->getCategories()->add($categories);
        $article->updateReferenceFields();

        $this->assertSame(array(
            'categories' => $ids,
        ), $article->queryForSave());
    }

    public function testDocumentEmbeddedsOneInsert()
    {
        $article = \Model\Article::create()
            ->setTitle('foo')
            ->setSource(\Model\Source::create()
                ->setName(123)
                ->setText(null)
                ->setInfo(\Model\Info::create()
                    ->setName(234)
                    ->setText(null)
                )
            )
        ;

        $this->assertSame(array(
            'title'  => 'foo',
            'source' => array(
                'name' => '123',
                'info' => array(
                    'name' => '234',
                ),
            ),
        ), $article->queryForSave());
    }

    public function testDocumentEmbeddedsOneUpdate()
    {
        $article = \Model\Article::create()->setDocumentData(array(
            '_id' => new \MongoId('123'),
            'title' => 'foo',
            'source' => array(
                'name' => 'bar',
                'text' => 'ups',
                'info' => array(
                    'name' => 'mon',
                    'text' => 'dongo',
                ),
            ),
        ));
        $article->setTitle('foobar');
        $article->getSource()->setName(234)->setText(null)->setLine(345)->setNote(null);
        $article->getSource()->getInfo()->setName(null)->setText(456)->setLine(null)->setNote(567);

        $this->assertSame(array(
            '$set' => array(
                'title' => 'foobar',
                'source.name' => '234',
                'source.line' => '345',
                'source.info.text' => '456',
                'source.info.note' => '567',
            ),
            '$unset' => array(
                'source.text' => 1,
                'source.info.name' => 1,
            ),
        ), $article->queryForSave());
    }

    public function testDocumentEmbeddedsOneChange()
    {
        $article = \Model\Article::create()->setDocumentData(array(
            '_id' => new \MongoId('123'),
            'title' => 'foo',
            'source' => array(
                'name' => 'bar',
                'info' => array(
                    'name' => 'mon',
                ),
            ),
        ));
        $article->setSource(\Model\Source::create()
            ->setText(234)
            ->setLine(345)
            ->setInfo(\Model\Info::create()
                ->setName(456)
                ->setText(567)
            )
        );

        $this->assertSame(array(
            '$set' => array(
                'source' => array(
                    'text' => '234',
                    'line' => '345',
                    'info' => array(
                        'name' => '456',
                        'text' => '567',
                    ),
                ),
            ),
        ), $article->queryForSave());
    }

    public function testDocumentEmbeddedsOneChangeDeep()
    {
        $article = \Model\Article::create()->setDocumentData(array(
            '_id' => new \MongoId('123'),
            'title' => 'foo',
            'source' => array(
                'name' => 'bar',
                'info' => array(
                    'name' => 'mon',
                ),
            ),
        ));
        $article->getSource()->setInfo(\Model\Info::create()
            ->setName(null)
            ->setText(234)
            ->setNote(345)
        );

        $this->assertSame(array(
            '$set' => array(
                'source.info' => array(
                    'text' => '234',
                    'note' => '345',
                ),
            ),
        ), $article->queryForSave());
    }

    public function testDocumentEmbeddedsOneRemove()
    {
        $article = \Model\Article::create()->setDocumentData(array(
            '_id' => new \MongoId('123'),
            'title' => 'foo',
            'source' => array(
                'name' => 'bar',
                'info' => array(
                    'name' => 'mon',
                ),
            ),
        ));
        $article->setSource(null);

        $this->assertSame(array(
            '$unset' => array(
                'source' => 1,
            ),
        ), $article->queryForSave());
    }

    public function testDocumentEmbeddedsOneRemoveDeep()
    {
        $article = \Model\Article::create()->setDocumentData(array(
            '_id' => new \MongoId('123'),
            'title' => 'foo',
            'source' => array(
                'name' => 'bar',
                'info' => array(
                    'name' => 'mon',
                ),
            ),
        ));
        $article->getSource()->setInfo(null);

        $this->assertSame(array(
            '$unset' => array(
                'source.info' => 1,
            ),
        ), $article->queryForSave());
    }

    public function testDocumentEmbeddedsManyInsert()
    {
        $comment1 = \Model\Comment::create()
            ->setName(123)
            ->setText(null)
            ->setNote('foo')
        ;
        $comment1->getInfos()->add(array(
            \Model\Info::create()
                ->setName(345)
                ->setText('foobar'),
            \Model\Info::create()
                ->setName(456)
                ->setLine('barfoo'),
        ));
        $comment1->getInfos()->remove(array(
            \Model\Info::create()->setName('ups')
        ));

        $article = \Model\Article::create()->setTitle('foo');
        $article->getComments()->add(array(
            $comment1,
            \Model\Comment::create()
                ->setName(234)
                ->setText('bar')
        ));
        $article->getComments()->remove(array(
            \Model\Comment::create()->setName(567)
        ));

        $this->assertSame(array(
            'title'  => 'foo',
            'comments' => array(
                array(
                    'name' => '123',
                    'note' => 'foo',
                    'infos' => array(
                        array(
                            'name' => '345',
                            'text' => 'foobar',
                        ),
                        array(
                            'name' => '456',
                            'line' => 'barfoo',
                        ),
                    ),
                ),
                array(
                    'name' => '234',
                    'text' => 'bar',
                ),
            ),
        ), $article->queryForSave());
    }

    public function testDocumentEmbeddedsManyUpdate()
    {
        $article = \Model\Article::create()->setDocumentData(array(
            '_id' => new \MongoId('123'),
            'title' => 'foo',
            'comments' => array(
                array(
                    'name' => 'bar',
                    'text' => 'ups',
                    'infos' => array(
                        array(
                            'name' => 'mon',
                            'note' => 'dongo',
                        ),
                        array(
                            'name' => 'man',
                            'text' => 'dango',
                        ),
                    ),
                ),
                array(
                    'name' => 'foobar',
                    'line' => 'barfoo'
                ),
                array(
                    'name' => 'removing1',
                ),
                array(
                    'name' => 'removing2',
                ),
            ),
        ));
        $article->setTitle('foobar');
        $comments = $article->getComments()->saved();
        $comments[0]->setName(234)->setText(null)->setLine(345)->setNote(null);
        $infos = $comments[0]->getInfos()->saved();
        $infos[0]->setName(456)->setText(567)->setNote(null)->setLine(null);
        $comments[0]->getInfos()->remove($infos[1]);
        $comments[1]->setName('mon')->setText('go');
        $article->getComments()->remove($comments[2]);
        $article->getComments()->remove($comments[3]);
        $article->getComments()->add(\Model\Comment::create()->setName('inserting1')->setText(123));
        $article->getComments()->add(\Model\Comment::create()->setName('inserting2')->setText(321));
        $comments[0]->getInfos()->add(\Model\Info::create()->setName('insertinfo1')->setNote(678));
        $comments[1]->getInfos()->add(\Model\Info::create()->setName('insertinfo2')->setNote(876));

        $this->assertSame(array(
            '$set' => array(
                'title' => 'foobar',
                'comments.0.name' => '234',
                'comments.0.line' => '345',
                'comments.0.infos.0.name' => '456',
                'comments.0.infos.0.text' => '567',
                'comments.1.name' => 'mon',
                'comments.1.text' => 'go',
            ),
            '$unset' => array(
                'comments.0.text' => 1,
                'comments.0.infos.0.note' => 1,
                'comments.0.infos.1' => 1,
                'comments.2' => 1,
                'comments.3' => 1,
            ),
            '$pushAll' => array(
                'comments.0.infos' => array(
                    array(
                        'name' => 'insertinfo1',
                        'note' => '678',
                    ),
                ),
                'comments.1.infos' => array(
                    array(
                        'name' => 'insertinfo2',
                        'note' => '876',
                    ),
                ),
                'comments' => array(
                    array(
                        'name' => 'inserting1',
                        'text' => '123',
                    ),
                    array(
                        'name' => 'inserting2',
                        'text' => '321',
                    ),
                ),
            ),
        ), $article->queryForSave());
    }
}
