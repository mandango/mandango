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
use Mandango\Group\EmbeddedGroup;

class CoreOnDeleteTest extends TestCase
{
    public function testReferenceOneCascade()
    {
        $author1 = $this->mandango->create('Model\Author')->setName('foo')->save();
        $author2 = $this->mandango->create('Model\Author')->setName('foo')->save();

        $article1 = $this->createArticle()->setAuthor($author1)->save();
        $article2 = $this->createArticle()->setAuthor($author2)->save();
        $article3 = $this->createArticle()->setAuthor($author1)->save();

        $article1Id = $article1->getId();
        $article2Id = $article2->getId();
        $article3Id = $article3->getId();

        $author1->delete();

        $this->assertTrue($article1->isNew());
        $this->assertFalse($article2->isNew());
        $this->assertTrue($article3->isNew());

        $collection = $article1->getRepository()->getCollection();
        $this->assertNull($collection->findOne(array('_id' => $article1Id)));
        $this->assertNotNull($collection->findOne(array('_id' => $article2Id)));
        $this->assertNull($collection->findOne(array('_id' => $article3Id)));
    }

    public function testReferenceOneUnset()
    {
        $information1 = $this->mandango->create('Model\ArticleInformation')->setName('foo')->save();
        $information2 = $this->mandango->create('Model\ArticleInformation')->setName('foo')->save();

        $article1 = $this->createArticle()->setInformation($information1)->save();
        $article2 = $this->createArticle()->setInformation($information2)->save();
        $article3 = $this->createArticle()->setInformation($information1)->save();

        $information1->delete();

        $article1->refresh();
        $article2->refresh();
        $article3->refresh();

        $this->assertNull($article1->getInformation());
        $this->assertNotNull($article2->getInformation());
        $this->assertNull($article3->getInformation());
    }

    public function testReferenceOnePolymorphicCascade()
    {
        $author1 = $this->mandango->create('Model\Author')->setName('foo')->save();
        $information1 = $this->mandango->create('Model\ArticleInformation')->setName('foo')->save();

        $article1 = $this->createArticle()->setLike($author1)->save();
        $article2 = $this->createArticle()->setLike($information1)->save();
        $article3 = $this->createArticle()->setLike($author1)->save();

        $article1Id = $article1->getId();
        $article2Id = $article2->getId();
        $article3Id = $article3->getId();

        $author1->delete();

        $this->assertTrue($article1->isNew());
        $this->assertFalse($article2->isNew());
        $this->assertTrue($article3->isNew());

        $collection = $article1->getRepository()->getCollection();
        $this->assertNull($collection->findOne(array('_id' => $article1Id)));
        $this->assertNotNull($collection->findOne(array('_id' => $article2Id)));
        $this->assertNull($collection->findOne(array('_id' => $article3Id)));
    }

    public function testReferenceOnePolymorphicUnset()
    {
        $author1 = $this->mandango->create('Model\Author')->setName('foo')->save();
        $information1 = $this->mandango->create('Model\ArticleInformation')->setName('foo')->save();

        $article1 = $this->createArticle()->setLikeUnset($author1)->save();
        $article2 = $this->createArticle()->setLikeUnset($information1)->save();
        $article3 = $this->createArticle()->setLikeUnset($author1)->save();

        $author1->delete();

        $article1->refresh();
        $article2->refresh();
        $article3->refresh();

        $this->assertNull($article1->getLikeUnset());
        $this->assertNotNull($article2->getLikeUnset());
        $this->assertNull($article3->getLikeUnset());
    }

    public function testReferenceOnePolymorphicCascadeWithDiscriminatorMap()
    {
        $author1 = $this->mandango->create('Model\Author')->setName('foo')->save();
        $category1 = $this->mandango->create('Model\Category')->setName('foo')->save();

        $article1 = $this->createArticle()->setFriend($author1)->save();
        $article2 = $this->createArticle()->setFriend($category1)->save();
        $article3 = $this->createArticle()->setFriend($author1)->save();

        $article1Id = $article1->getId();
        $article2Id = $article2->getId();
        $article3Id = $article3->getId();

        $author1->delete();

        $this->assertTrue($article1->isNew());
        $this->assertFalse($article2->isNew());
        $this->assertTrue($article3->isNew());

        $collection = $article1->getRepository()->getCollection();
        $this->assertNull($collection->findOne(array('_id' => $article1Id)));
        $this->assertNotNull($collection->findOne(array('_id' => $article2Id)));
        $this->assertNull($collection->findOne(array('_id' => $article3Id)));
    }

    public function testReferenceOnePolymorphicUnsetWithDiscriminatorMap()
    {
        $author1 = $this->mandango->create('Model\Author')->setName('foo')->save();
        $category1 = $this->mandango->create('Model\Category')->setName('foo')->save();

        $article1 = $this->createArticle()->setFriendUnset($author1)->save();
        $article2 = $this->createArticle()->setFriendUnset($category1)->save();
        $article3 = $this->createArticle()->setFriendUnset($author1)->save();

        $author1->delete();

        $article1->refresh();
        $article2->refresh();
        $article3->refresh();

        $this->assertNull($article1->getFriendUnset());
        $this->assertNotNull($article2->getFriendUnset());
        $this->assertNull($article3->getFriendUnset());
    }

    public function testReferenceManyUnset()
    {
        $category1 = $this->mandango->create('Model\Category')->setName('foo')->save();
        $category2 = $this->mandango->create('Model\Category')->setName('foo')->save();
        $category3 = $this->mandango->create('Model\Category')->setName('foo')->save();

        $article1 = $this->createArticle()->addCategories(array($category1, $category3))->save();
        $article2 = $this->createArticle()->addCategories($category2)->save();
        $article3 = $this->createArticle()->addCategories($category1)->save();

        $category1->delete();

        $article1->refresh();
        $article2->refresh();
        $article3->refresh();

        $this->assertEquals(array($category3->getId()), $article1->getCategoryIds());
        $this->assertEquals(array($category2->getId()), $article2->getCategoryIds());
        $this->assertEquals(array(), $article3->getCategoryIds());
    }

    public function testReferenceManyPolymorphicUnset()
    {
        $category1 = $this->mandango->create('Model\Category')->setName('foo')->save();
        $category2 = $this->mandango->create('Model\Category')->setName('foo')->save();
        $author1 = $this->mandango->create('Model\Author')->setName('foo')->save();

        $article1 = $this->createArticle()->addRelated(array($category1, $author1))->save();
        $article2 = $this->createArticle()->addRelated($category2)->save();
        $article3 = $this->createArticle()->addRelated($category1)->save();

        $category1->delete();

        $article1->refresh();
        $article2->refresh();
        $article3->refresh();

        $this->assertEquals(array($author1), iterator_to_array($article1->getRelated()));
        $this->assertEquals(array($category2), iterator_to_array($article2->getRelated()));
        $this->assertEquals(array(), iterator_to_array($article3->getRelated()));
    }

    public function testReferenceManyPolymorphicUnsetWithDiscriminatorMap()
    {
        $element1 = $this->mandango->create('Model\FormElement')->setLabel('foo')->save();
        $element2 = $this->mandango->create('Model\TextareaFormElement')->setLabel('foo')->save();
        $element3 = $this->mandango->create('Model\RadioFormElement')->setLabel('foo')->save();

        $article1 = $this->createArticle()->addElements(array($element1, $element3))->save();
        $article2 = $this->createArticle()->addElements($element2)->save();
        $article3 = $this->createArticle()->addElements($element1)->save();

        $element1->delete();

        $article1->refresh();
        $article2->refresh();
        $article3->refresh();

        $this->assertEquals(array($element3), iterator_to_array($article1->getElements()));
        $this->assertEquals(array($element2), iterator_to_array($article2->getElements()));
        $this->assertEquals(array(), iterator_to_array($article3->getElements()));
    }
}