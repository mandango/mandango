<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Tests;

use Mandango\UnitOfWork;
use Mandango\Mandango;

class UnitOfWorkTest extends TestCase
{
    public function testPersist()
    {
        $unitOfWork = new UnitOfWork(new Mandango($this->metadataFactory, $this->cache));

        $this->assertFalse($unitOfWork->hasPendingForPersist());
        $this->assertFalse($unitOfWork->hasPending());

        $article = $this->mandango->create('Model\Article');
        $category1 = $this->mandango->create('Model\Category');
        $category2 = $this->mandango->create('Model\Category');
        $category3 = $this->mandango->create('Model\Category');

        $unitOfWork->persist($category1);
        $unitOfWork->persist(array($category3, $article));

        $this->assertTrue($unitOfWork->isPendingForPersist($article));
        $this->assertTrue($unitOfWork->isPendingForPersist($category1));
        $this->assertFalse($unitOfWork->isPendingForPersist($category2));
        $this->assertTrue($unitOfWork->isPendingForPersist($category3));

        $this->assertTrue($unitOfWork->hasPendingForPersist());
        $this->assertTrue($unitOfWork->hasPending());

        $unitOfWork->remove($article);
        $this->assertFalse($unitOfWork->isPendingForPersist($article));
    }

    public function testRemove()
    {
        $unitOfWork = new UnitOfWork(new Mandango($this->metadataFactory, $this->cache));

        $this->assertFalse($unitOfWork->hasPendingForRemove());
        $this->assertFalse($unitOfWork->hasPending());

        $article = $this->mandango->create('Model\Article');
        $category1 = $this->mandango->create('Model\Category');
        $category2 = $this->mandango->create('Model\Category');
        $category3 = $this->mandango->create('Model\Category');

        $unitOfWork->remove($category1);
        $unitOfWork->remove(array($category3, $article));

        $this->assertTrue($unitOfWork->isPendingForRemove($article));
        $this->assertTrue($unitOfWork->isPendingForRemove($category1));
        $this->assertFalse($unitOfWork->isPendingForRemove($category2));
        $this->assertTrue($unitOfWork->isPendingForRemove($category3));

        $this->assertTrue($unitOfWork->hasPendingForRemove());
        $this->assertTrue($unitOfWork->hasPending());

        $unitOfWork->persist($article);
        $this->assertFalse($unitOfWork->isPendingForRemove($article));
    }

    public function testHasPendingClear()
    {
        $unitOfWork = new UnitOfWork(new Mandango($this->metadataFactory, $this->cache));

        $this->assertFalse($unitOfWork->hasPending());

        $article = $this->mandango->create('Model\Article');
        $category1 = $this->mandango->create('Model\Category');
        $category2 = $this->mandango->create('Model\Category');

        $unitOfWork->persist($article);
        $unitOfWork->persist($category1);
        $unitOfWork->remove($category2);
        $this->assertTrue($unitOfWork->hasPending());

        $unitOfWork->clear();
        $this->assertFalse($unitOfWork->hasPending());
    }

    public function testCommit()
    {
        $article1 = $this->mandango->create('Model\Article');
        $article2 = $this->mandango->create('Model\Article');
        $author1 = $this->mandango->create('Model\Author');
        $author2 = $this->mandango->create('Model\Author');
        $category1 = $this->mandango->create('Model\Category');
        $category2 = $this->mandango->create('Model\Category');

        $articleRepository = $this->getMockBuilder('Model\ArticleRepository')->disableOriginalConstructor()->getMock();
        $articleRepository->expects($this->any())->method('save')->with(array(
            spl_object_hash($article1) => $article1,
            spl_object_hash($article2) => $article2,
        ));

        $authorRepository = $this->getMockBuilder('Model\AuthorRepository')->disableOriginalConstructor()->getMock();
        $authorRepository->expects($this->any())->method('save')->with(array(
            spl_object_hash($author1) => $author1,
        ));
        $authorRepository->expects($this->any())->method('delete')->with(array(
            spl_object_hash($author2) => $author2,
        ));

        $categoryRepository = $this->getMockBuilder('Model\CategoryRepository')->disableOriginalConstructor()->getMock();
        $categoryRepository->expects($this->once())->method('delete')->with(array(
            spl_object_hash($category1) => $category1,
            spl_object_hash($category2) => $category2,
        ));

        $callback = function($documentClass) use ($articleRepository, $authorRepository, $categoryRepository) {
            if ('Model\Article' == $documentClass) {
                return $articleRepository;
            }
            if ('Model\Author' == $documentClass) {
                return $authorRepository;
            }
            if ('Model\Category' == $documentClass) {
                return $categoryRepository;
            }
        };

        $mandango = $this->getMockBuilder('Mandango\Mandango')->disableOriginalConstructor()->getMock();
        $mandango->expects($this->any())->method('getRepository')->will($this->returnCallback($callback));

        $unitOfWork = new UnitOfWork($mandango);
        $unitOfWork->persist($article1);
        $unitOfWork->persist($article2);
        $unitOfWork->persist($author1);
        $unitOfWork->remove($author2);
        $unitOfWork->remove($category1);
        $unitOfWork->remove($category2);

        $unitOfWork->commit();
    }
}
