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

class CoreQueryForSaveTest extends TestCase
{
    public function testDocumentFieldsInsert()
    {
        $article = $this->mandango->create('Model\Article');
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
        $article = $this->mandango->create('Model\Article');
        $article->setDocumentData(array(
            '_id'      => new \MongoId($this->generateObjectId()),
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
        $article = $this->mandango->create('Model\Article')
            ->setAuthor($this->mandango->create('Model\Author')->setId($id = new \MongoId($this->generateObjectId()))->setIsNew(false))
        ;

        $this->assertSame(array(
            'author' => $id,
        ), $article->queryForSave());
    }

    public function testDocumentReferencesOneUpdate()
    {
        $article = $this->mandango->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId($this->generateObjectId()),
            'author' => new \MongoId($this->generateObjectId()),
        ));
        $article->setAuthor($this->mandango->create('Model\Author')->setId($id = new \MongoId($this->generateObjectId()))->setIsNew(false));

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
            $categories[] = $this->mandango->create('Model\Category')->setId($ids[] = new \MongoId($this->generateObjectId()));
        }

        $article = $this->mandango->create('Model\Article');
        $article->getCategories()->add($categories);
        $article->updateReferenceFields();

        $this->assertSame(array(
            'categories' => $ids,
        ), $article->queryForSave());
    }

    public function testDocumentEmbeddedsOneInsert()
    {
        $article = $this->mandango->create('Model\Article')
            ->setTitle('foo')
            ->setSource($this->mandango->create('Model\Source')
                ->setName(123)
                ->setText(null)
                ->setInfo($this->mandango->create('Model\Info')
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
        $article = $this->mandango->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId($this->generateObjectId()),
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
        $article = $this->mandango->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId($this->generateObjectId()),
            'title' => 'foo',
            'source' => array(
                'name' => 'bar',
                'info' => array(
                    'name' => 'mon',
                ),
            ),
        ));
        $article->setSource($this->mandango->create('Model\Source')
            ->setText(234)
            ->setLine(345)
            ->setInfo($this->mandango->create('Model\Info')
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
        $article = $this->mandango->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId($this->generateObjectId()),
            'title' => 'foo',
            'source' => array(
                'name' => 'bar',
                'info' => array(
                    'name' => 'mon',
                ),
            ),
        ));
        $article->getSource()->setInfo($this->mandango->create('Model\Info')
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
        $article = $this->mandango->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId($this->generateObjectId()),
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
        $article = $this->mandango->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId($this->generateObjectId()),
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
        $comment1 = $this->mandango->create('Model\Comment')
            ->setName(123)
            ->setText(null)
            ->setNote('foo')
        ;
        $comment1->getInfos()->add(array(
            $this->mandango->create('Model\Info')
                ->setName(345)
                ->setText('foobar'),
            $this->mandango->create('Model\Info')
                ->setName(456)
                ->setLine('barfoo'),
        ));
        $comment1->getInfos()->remove(array(
            $this->mandango->create('Model\Info')->setName('ups')
        ));

        $article = $this->mandango->create('Model\Article')->setTitle('foo');
        $article->getComments()->add(array(
            $comment1,
            $this->mandango->create('Model\Comment')
                ->setName(234)
                ->setText('bar')
        ));
        $article->getComments()->remove(array(
            $this->mandango->create('Model\Comment')->setName(567)
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
        $article = $this->mandango->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId($this->generateObjectId()),
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
        $comments = $article->getComments()->getSaved();
        $comments[0]->setName(234)->setText(null)->setLine(345)->setNote(null);
        $infos = $comments[0]->getInfos()->getSaved();
        $infos[0]->setName(456)->setText(567)->setNote(null)->setLine(null);
        $comments[0]->getInfos()->remove($infos[1]);
        $comments[1]->setName('mon')->setText('go');
        $article->getComments()->remove($comments[2]);
        $article->getComments()->remove($comments[3]);
        $article->getComments()->add($this->mandango->create('Model\Comment')->setName('inserting1')->setText(123));
        $article->getComments()->add($this->mandango->create('Model\Comment')->setName('inserting2')->setText(321));
        $comments[0]->getInfos()->add($this->mandango->create('Model\Info')->setName('insertinfo1')->setNote(678));
        $comments[1]->getInfos()->add($this->mandango->create('Model\Info')->setName('insertinfo2')->setNote(876));

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
