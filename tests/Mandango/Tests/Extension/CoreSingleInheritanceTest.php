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

class CoreSingleInheritanceTest extends TestCase
{
    public function testDocumentParentClasses()
    {
        $this->assertTrue(is_subclass_of('Model\TextareaFormElement', 'Model\FormElement'));
        $this->assertTrue(is_subclass_of('Model\RadioFormElement', 'Model\FormElement'));
    }

    public function testDocumentSetDocumentData()
    {
        $formElement = new \Model\FormElement();
        $formElement->setDocumentData(array(
            'label'   => 123,
            'default' => 234,
        ));
        $this->assertSame('123', $formElement->getLabel());
        $this->assertSame(234, $formElement->getDefault());

        $textareaFormElement = new \Model\TextareaFormElement();
        $textareaFormElement->setDocumentData(array(
            'label'   => 234,
            'default' => 345,
        ));
        $this->assertSame('234', $textareaFormElement->getLabel());
        $this->assertSame('345', $textareaFormElement->getDefault());

        $radioFormElement = new \Model\RadioFormElement();
        $radioFormElement->setDocumentData(array(
            'label'   => 345,
            'default' => 'foobar',
            'options' => serialize($options = array('foobar' => 'Foo', 'barfoo' => 'Bar')),
        ));
        $this->assertSame('345', $radioFormElement->getLabel());
        $this->assertSame('foobar', $radioFormElement->getDefault());
        $this->assertSame($options, $radioFormElement->getOptions());
    }

    public function testDocumentSet()
    {
        $document = \Model\TextareaFormElement::create()->set('label', 'foo')->set('default', 'bar');
        $this->assertSame('foo', $document->getLabel());
        $this->assertSame('bar', $document->getDefault());

        $options = array('foo' => 'bar');
        $document = \Model\RadioFormElement::create()->set('label', 'foo')->set('options', $options);
        $this->assertSame('foo', $document->getLabel());
        $this->assertSame($options, $document->getOptions());
    }

    /**
     * @expectedException \invalidArgumentException
     */
    public function testDocumentSetFieldNotExist()
    {
        \Model\RadioFormElement::create()->set('no', 'foo');
    }

    public function testDocumentGet()
    {
        $document = \Model\TextareaFormElement::create()->setLabel('foo')->setDefault('bar');
        $this->assertSame('foo', $document->get('label'));
        $this->assertSame('bar', $document->get('default'));

        $options = array('foo' => 'bar');
        $document = \Model\RadioFormElement::create()->setLabel('foo')->setOptions($options);
        $this->assertSame('foo', $document->get('label'));
        $this->assertSame($options, $document->get('options'));
    }

    /**
     * @expectedException \invalidArgumentException
     */
    public function testDocumentGetFieldNotExist()
    {
        \Model\RadioFormElement::create()->get('no');
    }

    public function testDocumentFromArray()
    {
        $document = \Model\TextareaFormElement::create()->fromArray(array(
            'label'   => 'foo',
            'default' => 'bar',
        ));
        $this->assertSame('foo', $document->getLabel());
        $this->assertSame('bar', $document->getDefault());

        $options = array('foo' => 'bar');
        $document = \Model\RadioFormElement::create()->fromArray(array(
            'label'   => 'foo',
            'options' => $options,
        ));
        $this->assertSame('foo', $document->getLabel());
        $this->assertSame($options, $document->getOptions());
    }

    public function testDocumentToArray()
    {
        $document = \Model\TextareaFormElement::create()->setLabel('foo')->setDefault('bar');
        $this->assertSame(array(
            'label'   => 'foo',
            'default' => null,
            'default' => 'bar',
        ), $document->toArray());

        $options = array('foo' => 'bar');
        $document = \Model\RadioFormElement::create()->setLabel('foo')->setOptions($options);
        $this->assertSame(array(
            'label'   => 'foo',
            'default' => null,
            'options' => $options,
        ), $document->toArray());
    }

    public function testDocumentQueryForSave()
    {
        $formElement = \Model\FormElement::create()->setLabel(123)->setDefault(234);
        $this->assertSame(array(
            'label'   => '123',
            'default' => 234,
        ), $formElement->queryForSave());
        $formElement->clearModified();
        $formElement->setId(new \MongoId('123'));
        $this->assertSame(array(), $formElement->queryForSave());

        $textareaFormElement = \Model\TextareaFormElement::create()->setLabel(345)->setDefault(456);
        $this->assertSame(array(
            'type'    => 'textarea',
            'label'   => '345',
            'default' => '456',
        ), $textareaFormElement->queryForSave());
        $textareaFormElement->clearModified();
        $textareaFormElement->setId(new \MongoId('123'));
        $this->assertSame(array(), $textareaFormElement->queryForSave());

        $options = array('foobar' => 'foo', 'barfoo' => 'bar');
        $radioFormElement = \Model\RadioFormElement::create()->setLabel(567)->setDefault(678)->setOptions($options);
        $this->assertSame(array(
            'type'    => 'radio',
            'label'   => '567',
            'default' => 678,
            'options' => serialize($options),
        ), $radioFormElement->queryForSave());
    }

    public function testRepositoryCollectionName()
    {
        $this->assertSame('model_formelement', \Model\FormElement::getRepository()->getCollectionName());
        $this->assertSame('model_formelement', \Model\TextareaFormElement::getRepository()->getCollectionName());
        $this->assertSame('model_formelement', \Model\RadioFormElement::getRepository()->getCollectionName());
    }

    public function testRepositoryCount()
    {
        $formElements = array();
        for ($i = 0; $i < 5; $i++) {
            $formElements[] = \Model\FormElement::create()->setLabel('Element'.$i)->save();
        }

        $textareaFormElements = array();
        for ($i = 0; $i < 3; $i++) {
            $textareaFormElements[] = \Model\TextareaFormElement::create()->setLabel('Textarea'.$i)->save();
        }

        $radioFormElements = array();
        for ($i = 0; $i < 1; $i++) {
            $radioFormElements[] = \Model\RadioFormElement::create()->setLabel('Radio'.$i)->save();
        }

        $this->assertSame(9, \Model\FormElement::getRepository()->count());
        $this->assertSame(3, \Model\FormElement::getRepository()->count(array('label' => new \MongoRegex('/^Text/'))));
        $this->assertSame(3, \Model\TextareaFormElement::getRepository()->count());
        $this->assertSame(0, \Model\TextareaFormElement::getRepository()->count(array('label' => new \MongoRegex('/^R/'))));
        $this->assertSame(1, \Model\RadioFormElement::getRepository()->count());
    }

    public function testRepositoryRemove()
    {
        $formElements = array();
        for ($i = 0; $i < 5; $i++) {
            $formElements[] = \Model\FormElement::create()->setLabel('Element'.$i)->save();
        }

        $textareaFormElements = array();
        for ($i = 0; $i < 3; $i++) {
            $textareaFormElements[] = \Model\TextareaFormElement::create()->setLabel('Textarea'.$i)->save();
        }

        $radioFormElements = array();
        for ($i = 0; $i < 1; $i++) {
            $radioFormElements[] = \Model\RadioFormElement::create()->setLabel('Radio'.$i)->save();
        }

        \Model\FormElement::getRepository()->remove(array('label' => 'Textarea0'));
        $this->assertSame(8, \Model\FormElement::getRepository()->getCollection()->count());
        \Model\TextareaFormElement::getRepository()->remove(array('label' => new \MongoRegex('/^Element/')));
        $this->assertSame(8, \Model\FormElement::getRepository()->getCollection()->count());
        \Model\TextareaFormElement::getRepository()->remove();
        $this->assertSame(6, \Model\TextareaFormElement::getRepository()->getCollection()->count());
    }

    public function testQueryAll()
    {
        $formElements = array();
        for ($i = 0; $i < 5; $i++) {
            $formElements[] = \Model\FormElement::create()->setLabel('Element'.$i)->save();
        }

        $textareaFormElements = array();
        for ($i = 0; $i < 5; $i++) {
            $textareaFormElements[] = \Model\TextareaFormElement::create()->setLabel('Textarea'.$i)->save();
        }

        $radioFormElements = array();
        for ($i = 0; $i < 5; $i++) {
            $radioFormElements[] = \Model\RadioFormElement::create()->setLabel('Radio'.$i)->save();
        }

        \Model\FormElement::getRepository()->getIdentityMap()->clear();
        \Model\TextareaFormElement::getRepository()->getIdentityMap()->clear();
        \Model\RadioFormElement::getRepository()->getIdentityMap()->clear();

        // different classes in root class
        $document = \Model\FormElement::getRepository()->createQuery(array('_id' => $formElements[0]->getId()))->one();
        $this->assertInstanceof('Model\FormElement', $document);
        $this->assertEquals($formElements[0]->getId(), $document->getId());
        $document = \Model\FormElement::getRepository()->createQuery(array('_id' => $textareaFormElements[0]->getId()))->one();
        $this->assertInstanceof('Model\TextareaFormElement', $document);
        $this->assertEquals($textareaFormElements[0]->getId(), $document->getId());
        $document = \Model\FormElement::getRepository()->createQuery(array('_id' => $radioFormElements[0]->getId()))->one();
        $this->assertInstanceof('Model\RadioFormElement', $document);
        $this->assertEquals($radioFormElements[0]->getId(), $document->getId());

        // with and without identity map
        $ids = array(
            $formElements[0]->getId(),
            $textareaFormElements[0]->getId(),
            $radioFormElements[0]->getId(),
            $formElements[1]->getId(),
            $textareaFormElements[1]->getId(),
            $radioFormElements[1]->getId(),
        );
        $documents = \Model\FormElement::getRepository()->createQuery(array('_id' => array('$in' => $ids)))->all();
        $this->assertSame(6, count($documents));

        $id = $formElements[0]->getId();
        $this->assertTrue(isset($documents[$id->__toString()]));
        $document = $documents[$id->__toString()];
        $this->assertInstanceof('Model\FormElement', $document);
        $this->assertEquals($id, $document->getId());

        $id = $textareaFormElements[0]->getId();
        $this->assertTrue(isset($documents[$id->__toString()]));
        $document = $documents[$id->__toString()];
        $this->assertInstanceof('Model\TextareaFormElement', $document);
        $this->assertEquals($id, $document->getId());

        $id = $radioFormElements[0]->getId();
        $this->assertTrue(isset($documents[$id->__toString()]));
        $document = $documents[$id->__toString()];
        $this->assertInstanceof('Model\RadioFormElement', $document);
        $this->assertEquals($id, $document->getId());

        $id = $formElements[1]->getId();
        $this->assertTrue(isset($documents[$id->__toString()]));
        $document = $documents[$id->__toString()];
        $this->assertInstanceof('Model\FormElement', $document);
        $this->assertEquals($id, $document->getId());

        $id = $textareaFormElements[1]->getId();
        $this->assertTrue(isset($documents[$id->__toString()]));
        $document = $documents[$id->__toString()];
        $this->assertInstanceof('Model\TextareaFormElement', $document);
        $this->assertEquals($id, $document->getId());

        $id = $radioFormElements[1]->getId();
        $this->assertTrue(isset($documents[$id->__toString()]));
        $document = $documents[$id->__toString()];
        $this->assertInstanceof('Model\RadioFormElement', $document);
        $this->assertEquals($id, $document->getId());

        // no root class
        $document = \Model\TextareaFormElement::getRepository()->createQuery(array('_id' => $id = $textareaFormElements[0]->getId()))->one();
        $this->assertInstanceOf('Model\TextareaFormElement', $document);
        $this->assertEquals($id, $document->getId());
        $document = \Model\TextareaFormElement::getRepository()->createQuery(array('_id' => $formElements[0]->getId()))->one();
        $this->assertNull($document);
        $document = \Model\TextareaFormElement::getRepository()->createQuery(array('_id' => $radioFormElements[0]->getId()))->one();
        $this->assertNull($document);
        $document = \Model\RadioFormElement::getRepository()->createQuery(array('_id' => $formElements[0]->getId()))->one();
        $this->assertNull($document);
    }

    public function testQueryCount()
    {
        $formElements = array();
        for ($i = 0; $i < 5; $i++) {
            $formElements[] = \Model\FormElement::create()->setLabel('Element'.$i)->save();
        }

        $textareaFormElements = array();
        for ($i = 0; $i < 3; $i++) {
            $textareaFormElements[] = \Model\TextareaFormElement::create()->setLabel('Textarea'.$i)->save();
        }

        $radioFormElements = array();
        for ($i = 0; $i < 1; $i++) {
            $radioFormElements[] = \Model\RadioFormElement::create()->setLabel('Radio'.$i)->save();
        }

        $this->assertSame(9, \Model\FormElement::getRepository()->createQuery()->count());
        $this->assertSame(3, \Model\TextareaFormElement::getRepository()->createQuery()->count());
        $this->assertSame(1, \Model\RadioFormElement::getRepository()->createQuery()->count());
    }

    public function testEvents()
    {
        $formElement = \Model\FormElement::create()->setLabel('Element')->save();
        $this->assertSame(array(
            'ElementPreInserting',
            'ElementPostInserting',
        ), $formElement->getEvents());

        $textareaFormElement = \Model\TextareaFormElement::create()->setLabel('Textarea')->save();
        $this->assertSame(array(
            'ElementPreInserting',
            'TextareaPreInserting',
            'ElementPostInserting',
            'TextareaPostInserting',
        ), $textareaFormElement->getEvents());
    }
}
