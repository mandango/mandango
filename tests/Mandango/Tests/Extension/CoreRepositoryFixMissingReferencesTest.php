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

class CoreRepositoryFixMissingReferencesTest extends TestCase
{
    /**
     * @dataProvider fixMissingReferencesDataProvider
     */
    public function testReferencesOne($documentsPerBatch)
    {
        $author1 = $this->mandango->create('Model\Author')->setName('foo')->save();
        $author2 = $this->mandango->create('Model\Author')->setName('foo')->save();

        $article1 = $this->createArticle()->setAuthor($author1)->save();
        $article2 = $this->createArticle()->setAuthor($author2)->save();
        $article3 = $this->createArticle()->setAuthor($author1)->save();

        $this->removeFromCollection($author1);

        $article1->getRepository()->fixMissingReferences($documentsPerBatch);

        $this->assertFalse($this->documentExists($article1));
        $this->assertTrue($this->documentExists($article2));
        $this->assertFalse($this->documentExists($article3));
    }

    /**
     * @dataProvider fixMissingReferencesDataProvider
     */
    public function testReferencesMany($documentsPerBatch)
    {
        $category1 = $this->mandango->create('Model\Category')->setName('foo')->save();
        $category2 = $this->mandango->create('Model\Category')->setName('foo')->save();
        $category3 = $this->mandango->create('Model\Category')->setName('foo')->save();

        $article1 = $this->createArticle()->addCategories(array($category1, $category3))->save();
        $article2 = $this->createArticle()->addCategories($category2)->save();
        $article3 = $this->createArticle()->addCategories($category1)->save();

        $this->removeFromCollection($category1);

        $article1->getRepository()->fixMissingReferences($documentsPerBatch);

        $article1->refresh();
        $article2->refresh();
        $article3->refresh();

        $this->assertEquals(array($category3->getId()), $article1->getCategoryIds());
        $this->assertEquals(array($category2->getId()), $article2->getCategoryIds());
        $this->assertEquals(array(), $article3->getCategoryIds());
    }
}