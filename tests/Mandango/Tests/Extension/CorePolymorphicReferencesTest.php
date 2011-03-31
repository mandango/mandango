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

class CorePolymorphicReferencesTest extends TestCase
{
    public function testDocumentReferencesOneSetterGetter()
    {
        $article = \Model\Article::create();

        $author = \Model\Author::create();
        $category = \Model\Category::create();

        $article->setLike($author);
        $this->assertSame($author, $article->getLike());
        $article->setLike($category);
        $this->assertSame($category, $article->getLike());
    }

    public function testDocumentReferencesOneGetterQuery()
    {
        $author = \Model\Author::create()->setName('foo')->save();

        $article = \Model\Article::create();
        $this->assertNull($article->getLike());
        $article->setLikeRef(array(
            '_mandango_document_class' => 'Model\Author',
            'id' => $author->getId(),
        ));
        $this->assertSame($author, $article->getLike());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDocumentReferencesOneSetterInvalidClass()
    {
        \Model\Article::create()->setLike(new \DateTime());
    }

    public function testDocumentUpdateReferenceFieldsReferencesOne()
    {
        $author = \Model\Author::create();
        $article = \Model\Article::create()->setLike($author);
        $author->setId(new \MongoId('123'));
        $article->updateReferenceFields();
        $this->assertSame(array(
            '_mandango_document_class' => 'Model\Author',
            'id' => $author->getId(),
        ), $article->getLikeRef());
    }

    public function testDocumentReferencesManyGetter()
    {
        $article = \Model\Article::create();
        $related = $article->getRelated();
        $this->assertInstanceOf('Mandango\Group\PolymorphicReferenceGroup', $related);
        $this->assertSame('_mandango_document_class', $related->getDiscriminatorField());
        $this->assertSame($article, $related->getParent());
        $this->assertSame('related_ref', $related->getField());
        $this->assertSame($related, $article->getRelated());
    }

    public function testDocumentUpdateReferenceFieldsReferencesManyNew()
    {
        $article = \Model\Article::create();
        $related = $article->getRelated();
        $author1 = \Model\Author::create()->setId(new \MongoId('1'));
        $author2 = \Model\Author::create()->setId(new \MongoId('2'));
        $category1 = \Model\Category::create()->setId(new \MongoId('3'));
        $user1 = \Model\User::create()->setId(new \MongoId('4'));
        $related->add(array($author1, $author2, $category1, $user1));

        $article->updateReferenceFields();
        $this->assertSame(array(
            array('_mandango_document_class' => 'Model\Author', 'id' => $author1->getId()),
            array('_mandango_document_class' => 'Model\Author', 'id' => $author2->getId()),
            array('_mandango_document_class' => 'Model\Category', 'id' => $category1->getId()),
            array('_mandango_document_class' => 'Model\User', 'id' => $user1->getId()),
        ), $article->getRelatedRef());
    }

    public function testDocumentUpdateReferenceFieldsReferencesManyNotNew()
    {
        $article = \Model\Article::create()->setDocumentData(array(
            '_id' => new \MongoId('123'),
            'related_ref' => $relatedRef = array(
                array('_mandango_document_class' => 'Model\Author', 'id' => new \MongoId('1')),
                array('_mandango_document_class' => 'Model\Author', 'id' => new \MongoId('2')),
                array('_mandango_document_class' => 'Model\Category', 'id' => new \MongoId('3')),
                array('_mandango_document_class' => 'Model\Category', 'id' => new \MongoId('4')),
            ),
        ));
        $related = $article->getRelated();
        $add = array();
        $related->add($add[] = \Model\User::create()->setId(new \MongoId('1')));
        $related->add($add[] = \Model\Author::create()->setId(new \MongoId('5')));
        $related->add($add[] = \Model\Author::create()->setId(new \MongoId('6')));
        $related->remove(\Model\Author::create()->setId($relatedRef[1]['id']));
        $related->remove(\Model\Category::create()->setId($relatedRef[3]['id']));

        $article->updateReferenceFields();
        $this->assertSame(array(
            array('_mandango_document_class' => 'Model\Author', 'id' => $relatedRef[0]['id']),
            array('_mandango_document_class' => 'Model\Category', 'id' => $relatedRef[2]['id']),
            array('_mandango_document_class' => get_class($add[0]), 'id' => $add[0]->getId()),
            array('_mandango_document_class' => get_class($add[1]), 'id' => $add[1]->getId()),
            array('_mandango_document_class' => get_class($add[2]), 'id' => $add[2]->getId()),
        ), $article->getRelatedRef());
    }

    /*
     * Related to Mandango\Group\PolymorphicReferenceMany
     */
    public function testReferencesManyQuery()
    {
        $authors = array();
        for ($i = 0; $i < 9; $i++) {
            $authors[] = \Model\Author::create()->setName('Author'.$i)->save();
        }
        $categories = array();
        for ($i = 0; $i < 9; $i++) {
            $categories[] = \Model\Category::create()->setName('Category'.$i)->save();
        }
        $users = array();
        for ($i = 0; $i < 9; $i++) {
            $users[] = \Model\User::create()->setUsername('User'.$i)->save();
        }

        $relatedRef = array();
        $relatedRef[] = array('_mandango_document_class' => 'Model\Author', 'id' => $authors[3]->getId());
        $relatedRef[] = array('_mandango_document_class' => 'Model\Author', 'id' => $authors[5]->getId());
        $relatedRef[] = array('_mandango_document_class' => 'Model\Category', 'id' => $categories[1]->getId());
        $relatedRef[] = array('_mandango_document_class' => 'Model\User', 'id' => $users[8]->getId());
        $article = \Model\Article::create()->setRelatedRef($relatedRef);
        $this->assertSame(array(
            $authors[3],
            $authors[5],
            $categories[1],
            $users[8],
        ), $article->getRelated()->saved());
    }
}
