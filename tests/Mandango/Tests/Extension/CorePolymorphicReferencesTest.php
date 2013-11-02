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

class CorePolymorphicReferencesTest extends TestCase
{
    public function testDocumentReferencesOneSetterGetter()
    {
        $article = $this->mandango->create('Model\Article');

        $author = $this->mandango->create('Model\Author')->setName('foo')->save();
        $category = $this->mandango->create('Model\Category')->setName('foo')->save();

        $this->assertSame($article, $article->setLike($author));
        $this->assertSame($author, $article->getLike());
        $this->assertSame(array(
            '_mandangoDocumentClass' => 'Model\Author',
            'id' => $author->getId(),
        ), $article->getLikeRef());
        $article->setLike($category);
        $this->assertSame(array(
            '_mandangoDocumentClass' => 'Model\Category',
            'id' => $category->getId(),
        ), $article->getLikeRef());
        $this->assertSame($category, $article->getLike());
    }

    public function testDocumentReferencesOneSetterGetterDiscriminatorMap()
    {
        $article = $this->mandango->create('Model\Article');

        $author = $this->mandango->create('Model\Author')->setName('foo')->save();
        $category = $this->mandango->create('Model\Category')->setName('foo')->save();

        $this->assertSame($article, $article->setFriend($author));
        $this->assertSame($author, $article->getFriend());
        $this->assertSame(array(
            'name' => 'au',
            'id' => $author->getId(),
        ), $article->getFriendRef());
        $article->setFriend($category);
        $this->assertSame(array(
            'name' => 'ct',
            'id' => $category->getId(),
        ), $article->getFriendRef());
        $this->assertSame($category, $article->getFriend());
    }

    public function testDocumentReferencesOneGetterQuery()
    {
        $author = $this->mandango->create('Model\Author')->setName('foo')->save();

        $article = $this->mandango->create('Model\Article');
        $this->assertNull($article->getLike());
        $article->setLikeRef(array(
            '_mandangoDocumentClass' => 'Model\Author',
            'id' => $author->getId(),
        ));
        $this->assertSame($author, $article->getLike());
    }

    public function testDocumentReferencesOneGetterQueryDiscriminatorMap()
    {
        $category = $this->mandango->create('Model\Category')->setName('foo')->save();

        $article = $this->mandango->create('Model\Article');
        $this->assertNull($article->getLike());
        $article->setFriendRef(array(
            'name' => 'ct',
            'id' => $category->getId(),
        ));
        $this->assertSame($category, $article->getFriend());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDocumentReferencesOneSetterInvalidClass()
    {
        $this->mandango->create('Model\Article')->setLike(new \DateTime());
    }

    public function testDocumentUpdateReferenceFieldsReferencesOne()
    {
        $author = $this->mandango->create('Model\Author');
        $article = $this->mandango->create('Model\Article')->setLike($author);
        $author->setId(new \MongoId($this->generateObjectId()));
        $article->updateReferenceFields();
        $this->assertSame(array(
            '_mandangoDocumentClass' => 'Model\Author',
            'id' => $author->getId(),
        ), $article->getLikeRef());
    }

    public function testDocumentUpdateReferenceFieldsReferencesOneDiscriminatorMap()
    {
        $author = $this->mandango->create('Model\Author');
        $article = $this->mandango->create('Model\Article')->setFriend($author);
        $author->setId(new \MongoId($this->generateObjectId()));
        $article->updateReferenceFields();
        $this->assertSame(array(
            'name' => 'au',
            'id' => $author->getId(),
        ), $article->getFriendRef());
    }

    public function testDocumentQueryForSaveReferencesOne()
    {
        $author = $this->mandango->create('Model\Author')->setName('pablodip')->save();
        $article = $this->mandango->create('Model\Article')->setLike($author);

        $this->assertSame(array(
            'like' => array(
                '_mandangoDocumentClass' => 'Model\Author',
                'id' => $author->getId(),
            ),
        ), $article->queryForSave());
    }

    public function testDocumentReferencesManyGetter()
    {
        $article = $this->mandango->create('Model\Article');
        $related = $article->getRelated();
        $this->assertInstanceOf('Mandango\Group\PolymorphicReferenceGroup', $related);
        $this->assertSame('_mandangoDocumentClass', $related->getDiscriminatorField());
        $this->assertSame($article, $related->getParent());
        $this->assertSame('relatedRef', $related->getField());
        $this->assertSame($related, $article->getRelated());
    }

    public function testDocumentUpdateReferenceFieldsReferencesManyNew()
    {
        $article = $this->mandango->create('Model\Article');
        $related = $article->getRelated();
        $author1 = $this->mandango->create('Model\Author')->setId(new \MongoId($this->generateObjectId()));
        $author2 = $this->mandango->create('Model\Author')->setId(new \MongoId($this->generateObjectId()));
        $category1 = $this->mandango->create('Model\Category')->setId(new \MongoId($this->generateObjectId()));
        $user1 = $this->mandango->create('Model\User')->setId(new \MongoId($this->generateObjectId()));
        $related->add(array($author1, $author2, $category1, $user1));

        $article->updateReferenceFields();
        $this->assertSame(array(
            array('_mandangoDocumentClass' => 'Model\Author', 'id' => $author1->getId()),
            array('_mandangoDocumentClass' => 'Model\Author', 'id' => $author2->getId()),
            array('_mandangoDocumentClass' => 'Model\Category', 'id' => $category1->getId()),
            array('_mandangoDocumentClass' => 'Model\User', 'id' => $user1->getId()),
        ), $article->getRelatedRef());
    }

    public function testDocumentUpdateReferenceFieldsReferencesManyNotNew()
    {
        $article = $this->mandango->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId($this->generateObjectId()),
            'related' => $relatedRef = array(
                array('_mandangoDocumentClass' => 'Model\Author', 'id' => new \MongoId($this->generateObjectId())),
                array('_mandangoDocumentClass' => 'Model\Author', 'id' => new \MongoId($this->generateObjectId())),
                array('_mandangoDocumentClass' => 'Model\Category', 'id' => new \MongoId($this->generateObjectId())),
                array('_mandangoDocumentClass' => 'Model\Category', 'id' => new \MongoId($this->generateObjectId())),
            ),
        ));
        $related = $article->getRelated();
        $add = array();
        $related->add($add[] = $this->mandango->create('Model\User')->setId(new \MongoId($this->generateObjectId())));
        $related->add($add[] = $this->mandango->create('Model\Author')->setId(new \MongoId($this->generateObjectId())));
        $related->add($add[] = $this->mandango->create('Model\Author')->setId(new \MongoId($this->generateObjectId())));
        $related->remove($this->mandango->create('Model\Author')->setId($relatedRef[1]['id']));
        $related->remove($this->mandango->create('Model\Category')->setId($relatedRef[3]['id']));

        $article->updateReferenceFields();
        $this->assertSame(array(
            array('_mandangoDocumentClass' => 'Model\Author', 'id' => $relatedRef[0]['id']),
            array('_mandangoDocumentClass' => 'Model\Category', 'id' => $relatedRef[2]['id']),
            array('_mandangoDocumentClass' => get_class($add[0]), 'id' => $add[0]->getId()),
            array('_mandangoDocumentClass' => get_class($add[1]), 'id' => $add[1]->getId()),
            array('_mandangoDocumentClass' => get_class($add[2]), 'id' => $add[2]->getId()),
        ), $article->getRelatedRef());
    }

    public function testDocumentUpdateReferenceFieldsReferencesManyNewDiscriminatorMap()
    {
        $article = $this->mandango->create('Model\Article');
        $elements = $article->getElements();
        $element1 = $this->mandango->create('Model\FormElement')->setId(new \MongoId($this->generateObjectId()));
        $element2 = $this->mandango->create('Model\FormElement')->setId(new \MongoId($this->generateObjectId()));
        $textareaElement1 = $this->mandango->create('Model\TextareaFormElement')->setId(new \MongoId($this->generateObjectId()));
        $radioElement1 = $this->mandango->create('Model\RadioFormElement')->setId(new \MongoId($this->generateObjectId()));
        $elements->add(array($element1, $element2, $textareaElement1, $radioElement1));

        $article->updateReferenceFields();
        $this->assertSame(array(
            array('type' => 'element', 'id' => $element1->getId()),
            array('type' => 'element', 'id' => $element2->getId()),
            array('type' => 'textarea', 'id' => $textareaElement1->getId()),
            array('type' => 'radio', 'id' => $radioElement1->getId()),
        ), $article->getElementsRef());
    }

    public function testDocumentUpdateReferenceFieldsReferencesManyNotNewDiscriminatorMap()
    {
        $article = $this->mandango->create('Model\Article')->setDocumentData(array(
            '_id' => new \MongoId($this->generateObjectId()),
            'elements' => $elementsRef = array(
                array('type' => 'element', 'id' => new \MongoId($this->generateObjectId())),
                array('type' => 'element', 'id' => new \MongoId($this->generateObjectId())),
                array('type' => 'textarea', 'id' => new \MongoId($this->generateObjectId())),
                array('type' => 'textarea', 'id' => new \MongoId($this->generateObjectId())),
            ),
        ));
        $elements = $article->getElements();
        $add = array();
        $elements->add($add[] = $this->mandango->create('Model\RadioFormElement')->setId(new \MongoId($this->generateObjectId())));
        $elements->add($add[] = $this->mandango->create('Model\FormElement')->setId(new \MongoId($this->generateObjectId())));
        $elements->add($add[] = $this->mandango->create('Model\FormElement')->setId(new \MongoId($this->generateObjectId())));
        $elements->remove($this->mandango->create('Model\FormElement')->setId($elementsRef[1]['id']));
        $elements->remove($this->mandango->create('Model\TextareaFormElement')->setId($elementsRef[3]['id']));

        $article->updateReferenceFields();
        $this->assertSame(array(
            array('type' => 'element', 'id' => $elementsRef[0]['id']),
            array('type' => 'textarea', 'id' => $elementsRef[2]['id']),
            array('type' => 'radio', 'id' => $add[0]->getId()),
            array('type' => 'element', 'id' => $add[1]->getId()),
            array('type' => 'element', 'id' => $add[2]->getId()),
        ), $article->getElementsRef());
    }

    /*
     * Related to Mandango\Group\PolymorphicReferenceMany
     */
    public function testReferencesManyQuery()
    {
        $authors = array();
        for ($i = 0; $i < 9; $i++) {
            $authors[] = $this->mandango->create('Model\Author')->setName('Author'.$i)->save();
        }
        $categories = array();
        for ($i = 0; $i < 9; $i++) {
            $categories[] = $this->mandango->create('Model\Category')->setName('Category'.$i)->save();
        }
        $users = array();
        for ($i = 0; $i < 9; $i++) {
            $users[] = $this->mandango->create('Model\User')->setUsername('User'.$i)->save();
        }

        $relatedRef = array();
        $relatedRef[] = array('_mandangoDocumentClass' => 'Model\Author', 'id' => $authors[3]->getId());
        $relatedRef[] = array('_mandangoDocumentClass' => 'Model\Author', 'id' => $authors[5]->getId());
        $relatedRef[] = array('_mandangoDocumentClass' => 'Model\Category', 'id' => $categories[1]->getId());
        $relatedRef[] = array('_mandangoDocumentClass' => 'Model\User', 'id' => $users[8]->getId());
        $article = $this->mandango->create('Model\Article')->setRelatedRef($relatedRef);
        $this->assertSame(array(
            $authors[3],
            $authors[5],
            $categories[1],
            $users[8],
        ), $article->getRelated()->getSaved());
    }

    public function testReferencesManyQueryDiscriminatorMap()
    {
        $elements = array();
        for ($i = 0; $i < 9; $i++) {
            $elements[] = $this->mandango->create('Model\FormElement')->setLabel('Element'.$i)->save();
        }
        $textareaElements = array();
        for ($i = 0; $i < 9; $i++) {
            $textareaElements[] = $this->mandango->create('Model\TextareaFormElement')->setLabel('Textarea'.$i)->save();
        }
        $radioElements = array();
        for ($i = 0; $i < 9; $i++) {
            $radioElements[] = $this->mandango->create('Model\RadioFormElement')->setLabel('Radio'.$i)->save();
        }

        $elementsRef = array();
        $elementsRef[] = array('type' => 'element', 'id' => $elements[3]->getId());
        $elementsRef[] = array('type' => 'element', 'id' => $elements[5]->getId());
        $elementsRef[] = array('type' => 'textarea', 'id' => $textareaElements[1]->getId());
        $elementsRef[] = array('type' => 'radio', 'id' => $radioElements[8]->getId());
        $article = $this->mandango->create('Model\Article')->setElementsRef($elementsRef);
        $this->assertSame(array(
            $elements[3],
            $elements[5],
            $textareaElements[1],
            $radioElements[8],
        ), $article->getElements()->getSaved());
    }

    public function testDocumentQueryForSaveReferencesMany()
    {
        $article = $this->mandango->create('Model\Article');
        $related = $article->getRelated();
        $author = $this->mandango->create('Model\Author')->setName('foo')->save();
        $category = $this->mandango->create('Model\Category')->setName('bar')->save();
        $related->add(array($author, $category));
        $article->updateReferenceFields();

        $this->assertSame(array(
            'related' => array(
                array(
                    '_mandangoDocumentClass' => 'Model\Author',
                    'id' => $author->getId(),
                ),
                array(
                    '_mandangoDocumentClass' => 'Model\Category',
                    'id' => $category->getId(),
                ),
            ),
        ), $article->queryForSave());
    }

    public function testBasicQueryForSave()
    {
        $element = $this->mandango->create('Model\FormElement')->setLabel('foo');
        $this->assertSame(array(
            'label' => 'foo',
            'type'  => 'formelement',
        ), $element->queryForSave());
        $element->save();
        $element->setLabel('bar');
        $this->assertSame(array(
            '$set' => array(
                'label' => 'bar',
            ),
        ), $element->queryForSave());

        $textareaElement = $this->mandango->create('Model\TextareaFormElement')->setLabel('ups');
        $this->assertSame(array(
            'label' => 'ups',
            'type' => 'textarea',
        ), $textareaElement->queryForSave());
        $textareaElement->save();
        $textareaElement->setLabel('zam');
        $this->assertSame(array(
            '$set' => array(
                'label' => 'zam',
            ),
        ), $textareaElement->queryForSave());
    }
}
