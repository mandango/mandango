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
        $formElement = $this->mandango->create('Model\FormElement');
        $formElement->setDocumentData(array(
            'label'   => 123,
            'default' => 234,
        ));
        $this->assertSame('123', $formElement->getLabel());
        $this->assertSame(234, $formElement->getDefault());

        $textareaFormElement = $this->mandango->create('Model\TextareaFormElement');
        $textareaFormElement->setDocumentData(array(
            'label'   => 234,
            'default' => 345,
        ));
        $this->assertSame('234', $textareaFormElement->getLabel());
        $this->assertSame('345', $textareaFormElement->getDefault());

        $radioFormElement = $this->mandango->create('Model\RadioFormElement');
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
        $document = $this->mandango->create('Model\TextareaFormElement')->set('label', 'foo')->set('default', 'bar');
        $this->assertSame('foo', $document->getLabel());
        $this->assertSame('bar', $document->getDefault());

        $options = array('foo' => 'bar');
        $document = $this->mandango->create('Model\RadioFormElement')->set('label', 'foo')->set('options', $options);
        $this->assertSame('foo', $document->getLabel());
        $this->assertSame($options, $document->getOptions());
    }

    /**
     * @expectedException \invalidArgumentException
     */
    public function testDocumentSetFieldNotExist()
    {
        $this->mandango->create('Model\RadioFormElement')->set('no', 'foo');
    }

    public function testDocumentGet()
    {
        $document = $this->mandango->create('Model\TextareaFormElement')->setLabel('foo')->setDefault('bar');
        $this->assertSame('foo', $document->get('label'));
        $this->assertSame('bar', $document->get('default'));

        $options = array('foo' => 'bar');
        $document = $this->mandango->create('Model\RadioFormElement')->setLabel('foo')->setOptions($options);
        $this->assertSame('foo', $document->get('label'));
        $this->assertSame($options, $document->get('options'));
    }

    /**
     * @expectedException \invalidArgumentException
     */
    public function testDocumentGetFieldNotExist()
    {
        $this->mandango->create('Model\RadioFormElement')->get('no');
    }

    public function testDocumentFromArray()
    {
        $document = $this->mandango->create('Model\TextareaFormElement')->fromArray(array(
            'label'   => 'foo',
            'default' => 'bar',
        ));
        $this->assertSame('foo', $document->getLabel());
        $this->assertSame('bar', $document->getDefault());

        $options = array('foo' => 'bar');
        $document = $this->mandango->create('Model\RadioFormElement')->fromArray(array(
            'label'   => 'foo',
            'options' => $options,
        ));
        $this->assertSame('foo', $document->getLabel());
        $this->assertSame($options, $document->getOptions());
    }

    public function testDocumentToArray()
    {
        $document = $this->mandango->create('Model\TextareaFormElement')->setLabel('foo')->setDefault('bar');
        $this->assertSame(array(
            'id'      => null,
            'label'   => 'foo',
            'default' => null,
            'default' => 'bar',
        ), $document->toArray());

        $options = array('foo' => 'bar');
        $document = $this->mandango->create('Model\RadioFormElement')->setLabel('foo')->setOptions($options);
        $this->assertSame(array(
            'id'      => null,
            'label'   => 'foo',
            'default' => null,
            'options' => $options,
        ), $document->toArray());
    }

    public function testDocumentQueryForSave()
    {
        $formElement = $this->mandango->create('Model\FormElement')->setLabel(123)->setDefault(234);
        $this->assertSame(array(
            'label'   => '123',
            'type'  => 'formelement',
            'default' => 234,
        ), $formElement->queryForSave());
        $formElement->clearModified();
        $formElement->setId(new \MongoId($this->generateObjectId()));
        $formElement->setIsNew(false);
        $this->assertSame(array(), $formElement->queryForSave());

        $textareaFormElement = $this->mandango->create('Model\TextareaFormElement')->setLabel(345)->setDefault(456);
        $this->assertSame(array(
            'label'   => '345',
            'type'    => 'textarea',
            'default' => '456',
        ), $textareaFormElement->queryForSave());
        $textareaFormElement->clearModified();
        $textareaFormElement->setId(new \MongoId($this->generateObjectId()));
        $textareaFormElement->setIsNew(false);
        $this->assertSame(array(), $textareaFormElement->queryForSave());

        $options = array('foobar' => 'foo', 'barfoo' => 'bar');
        $radioFormElement = $this->mandango->create('Model\RadioFormElement')->setLabel(567)->setDefault(678)->setOptions($options);
        $this->assertSame(array(
            'label'   => '567',
            'type'    => 'radio',
            'default' => 678,
            'options' => serialize($options),
        ), $radioFormElement->queryForSave());
    }

    public function testRepositoryCollectionName()
    {
        $this->assertSame('model_element', $this->mandango->getRepository('Model\FormElement')->getCollectionName());
        $this->assertSame('model_element', $this->mandango->getRepository('Model\TextareaFormElement')->getCollectionName());
        $this->assertSame('model_element', $this->mandango->getRepository('Model\RadioFormElement')->getCollectionName());
    }

    public function testRepositoryCount()
    {
        $elements = array();
        for ($i = 0; $i < 2; $i++) {
            $elements[] = $this->mandango->create('Model\Element')->setLabel('Element'.$i)->save();
        }

        $formElements = array();
        for ($i = 0; $i < 5; $i++) {
            $formElements[] = $this->mandango->create('Model\FormElement')->setLabel('FormElement'.$i)->save();
        }

        $textareaFormElements = array();
        for ($i = 0; $i < 3; $i++) {
            $textareaFormElements[] = $this->mandango->create('Model\TextareaFormElement')->setLabel('Textarea'.$i)->save();
        }

        $radioFormElements = array();
        for ($i = 0; $i < 1; $i++) {
            $radioFormElements[] = $this->mandango->create('Model\RadioFormElement')->setLabel('Radio'.$i)->save();
        }

        $textElements = array();
        for ($i = 0; $i < 5; $i++) {
            $textElements[] = $this->mandango->create('Model\TextElement')->setLabel('TextElement'.$i)->save();
        }

        $this->assertSame(16, $this->mandango->getRepository('Model\Element')->count());
        $this->assertSame(3, $this->mandango->getRepository('Model\Element')->count(array('label' => new \MongoRegex('/^Textarea/'))));
        $this->assertSame(8, $this->mandango->getRepository('Model\Element')->count(array('label' => new \MongoRegex('/^Text/'))));
        $this->assertSame(1, $this->mandango->getRepository('Model\Element')->count(array('label' => new \MongoRegex('/^Radio/'))));
        $this->assertSame(5, $this->mandango->getRepository('Model\Element')->count(array('label' => new \MongoRegex('/^Form/'))));
        $this->assertSame(9, $this->mandango->getRepository('Model\FormElement')->count());
        $this->assertSame(3, $this->mandango->getRepository('Model\FormElement')->count(array('label' => new \MongoRegex('/^Text/'))));
        $this->assertSame(3, $this->mandango->getRepository('Model\TextareaFormElement')->count());
        $this->assertSame(0, $this->mandango->getRepository('Model\TextareaFormElement')->count(array('label' => new \MongoRegex('/^R/'))));
        $this->assertSame(1, $this->mandango->getRepository('Model\RadioFormElement')->count());
        $this->assertSame(5, $this->mandango->getRepository('Model\TextElement')->count());
    }

    public function testRepositoryUpdate()
    {
        $elements = array();
        for ($i = 0; $i < 2; $i++) {
            $elements[] = $this->mandango->create('Model\Element')->setLabel('Element'.$i)->save();
        }

        $formElements = array();
        for ($i = 0; $i < 5; $i++) {
            $formElements[] = $this->mandango->create('Model\FormElement')->setLabel('FormElement'.$i)->save();
        }

        $textareaFormElements = array();
        for ($i = 0; $i < 3; $i++) {
            $textareaFormElements[] = $this->mandango->create('Model\TextareaFormElement')->setLabel('Textarea'.$i)->save();
        }

        $radioFormElements = array();
        for ($i = 0; $i < 1; $i++) {
            $radioFormElements[] = $this->mandango->create('Model\RadioFormElement')->setLabel('Radio'.$i)->save();
        }

        $newObject = array('$set' => array('label' => 'ups'));
        $criteria = array('label' => 'ups');
        $this->mandango->getRepository('Model\FormElement')->update(array('label' => 'Textarea0'), $newObject);
        $this->assertSame(1, $this->mandango->getRepository('Model\Element')->getCollection()->count($criteria));
        $this->assertSame(1, $this->mandango->getRepository('Model\FormElement')->getCollection()->count($criteria));
        $this->mandango->getRepository('Model\TextareaFormElement')->update(array('label' => new \MongoRegex('/^FormElement/')), $newObject);
        $this->assertSame(1, $this->mandango->getRepository('Model\FormElement')->getCollection()->count($criteria));
        $this->mandango->getRepository('Model\TextareaFormElement')->update(array(), $newObject);
        $this->assertSame(1, $this->mandango->getRepository('Model\TextareaFormElement')->getCollection()->count($criteria));
    }

    public function testRepositoryRemove()
    {
        $elements = array();
        for ($i = 0; $i < 2; $i++) {
            $elements[] = $this->mandango->create('Model\Element')->setLabel('Element'.$i)->save();
        }

        $formElements = array();
        for ($i = 0; $i < 5; $i++) {
            $formElements[] = $this->mandango->create('Model\FormElement')->setLabel('FormElement'.$i)->save();
        }

        $textareaFormElements = array();
        for ($i = 0; $i < 3; $i++) {
            $textareaFormElements[] = $this->mandango->create('Model\TextareaFormElement')->setLabel('Textarea'.$i)->save();
        }

        $radioFormElements = array();
        for ($i = 0; $i < 1; $i++) {
            $radioFormElements[] = $this->mandango->create('Model\RadioFormElement')->setLabel('Radio'.$i)->save();
        }

        $this->mandango->getRepository('Model\FormElement')->remove(array('label' => 'Textarea0'));
        $this->assertSame(10, $this->mandango->getRepository('Model\Element')->getCollection()->count());
        $this->assertSame(10, $this->mandango->getRepository('Model\FormElement')->getCollection()->count());
        $this->mandango->getRepository('Model\TextareaFormElement')->remove(array('label' => new \MongoRegex('/^FormElement/')));
        $this->assertSame(10, $this->mandango->getRepository('Model\FormElement')->getCollection()->count());
        $this->mandango->getRepository('Model\TextareaFormElement')->remove();
        $this->assertSame(8, $this->mandango->getRepository('Model\TextareaFormElement')->getCollection()->count());
    }

    public function testQueryAll()
    {
        $elements = array();
        for ($i = 0; $i < 2; $i++) {
            $elements[] = $this->mandango->create('Model\Element')->setLabel('Element'.$i)->save();
        }

        $formElements = array();
        for ($i = 0; $i < 5; $i++) {
            $formElements[] = $this->mandango->create('Model\FormElement')->setLabel('FormElement'.$i)->save();
        }

        $textareaFormElements = array();
        for ($i = 0; $i < 5; $i++) {
            $textareaFormElements[] = $this->mandango->create('Model\TextareaFormElement')->setLabel('Textarea'.$i)->save();
        }

        $radioFormElements = array();
        for ($i = 0; $i < 5; $i++) {
            $radioFormElements[] = $this->mandango->create('Model\RadioFormElement')->setLabel('Radio'.$i)->save();
        }

        $this->mandango->getRepository('Model\FormElement')->getIdentityMap()->clear();
        $this->mandango->getRepository('Model\TextareaFormElement')->getIdentityMap()->clear();
        $this->mandango->getRepository('Model\RadioFormElement')->getIdentityMap()->clear();

        // different classes in root class
        $document = $this->mandango->getRepository('Model\Element')->createQuery(array('_id' => $formElements[0]->getId()))->one();
        $this->assertInstanceof('Model\FormElement', $document);
        $this->assertEquals($formElements[0]->getId(), $document->getId());
        $document = $this->mandango->getRepository('Model\Element')->createQuery(array('_id' => $radioFormElements[0]->getId()))->one();
        $this->assertInstanceof('Model\RadioFormElement', $document);
        $this->assertEquals($radioFormElements[0]->getId(), $document->getId());
        $document = $this->mandango->getRepository('Model\FormElement')->createQuery(array('_id' => $formElements[0]->getId()))->one();
        $this->assertInstanceof('Model\FormElement', $document);
        $this->assertEquals($formElements[0]->getId(), $document->getId());
        $document = $this->mandango->getRepository('Model\FormElement')->createQuery(array('_id' => $textareaFormElements[0]->getId()))->one();
        //$this->assertInstanceof('Model\TextareaFormElement', $document);
        $this->assertEquals($textareaFormElements[0]->getId(), $document->getId());
        $document = $this->mandango->getRepository('Model\FormElement')->createQuery(array('_id' => $radioFormElements[0]->getId()))->one();
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
        $documents = $this->mandango->getRepository('Model\Element')->createQuery(array('_id' => array('$in' => $ids)))->all();
        $this->assertSame(6, count($documents));

        $documents = $this->mandango->getRepository('Model\FormElement')->createQuery(array('_id' => array('$in' => $ids)))->all();
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
        $document = $this->mandango->getRepository('Model\TextareaFormElement')->createQuery(array('_id' => $id = $textareaFormElements[0]->getId()))->one();
        $this->assertInstanceOf('Model\TextareaFormElement', $document);
        $this->assertEquals($id, $document->getId());
        $document = $this->mandango->getRepository('Model\TextareaFormElement')->createQuery(array('_id' => $formElements[0]->getId()))->one();
        $this->assertNull($document);
        $document = $this->mandango->getRepository('Model\TextareaFormElement')->createQuery(array('_id' => $radioFormElements[0]->getId()))->one();
        $this->assertNull($document);
        $document = $this->mandango->getRepository('Model\RadioFormElement')->createQuery(array('_id' => $formElements[0]->getId()))->one();
        $this->assertNull($document);
    }

    public function testQueryCount()
    {
        $formElements = array();
        for ($i = 0; $i < 5; $i++) {
            $formElements[] = $this->mandango->create('Model\FormElement')->setLabel('Element'.$i)->save();
        }

        $textareaFormElements = array();
        for ($i = 0; $i < 3; $i++) {
            $textareaFormElements[] = $this->mandango->create('Model\TextareaFormElement')->setLabel('Textarea'.$i)->save();
        }

        $radioFormElements = array();
        for ($i = 0; $i < 1; $i++) {
            $radioFormElements[] = $this->mandango->create('Model\RadioFormElement')->setLabel('Radio'.$i)->save();
        }

        $textElements = array();
        for ($i = 0; $i < 5; $i++) {
            $textElements[] = $this->mandango->create('Model\TextElement')->setLabel('TextElement'.$i)->save();
        }

        $this->assertSame(14, $this->mandango->getRepository('Model\Element')->createQuery()->count());
        $this->assertSame(5, $this->mandango->getRepository('Model\TextElement')->createQuery()->count());
        $this->assertSame(9, $this->mandango->getRepository('Model\FormElement')->createQuery()->count());
        $this->assertSame(3, $this->mandango->getRepository('Model\TextareaFormElement')->createQuery()->count());
        $this->assertSame(1, $this->mandango->getRepository('Model\RadioFormElement')->createQuery()->count());
    }

    public function testParentEvents()
    {
        $formElement = $this->mandango->create('Model\FormElement')->setLabel('Element')->save();
        $this->assertSame(array(
            'ElementPreInserting',
            'FormElementPreInserting',
            'ElementPostInserting',
            'FormElementPostInserting',
        ), $formElement->getEvents());

        $formElement = $this->mandango->create('Model\TextTextElement')->setLabel('Element')->save();
        $this->assertSame(array(
            'ElementPreInserting',
            'TextTextElementPreInsert',
            'ElementPostInserting',
        ), $formElement->getEvents());
    }

    public function testGrandParentEvents()
    {

        $textareaFormElement = $this->mandango->create('Model\TextareaFormElement')->setLabel('Textarea')->save();
        $this->assertSame(array(
            'ElementPreInserting',
            'FormElementPreInserting',
            'TextareaPreInserting',
            'ElementPostInserting',
            'FormElementPostInserting',
            'TextareaPostInserting',
        ), $textareaFormElement->getEvents());
    }

    public function testRepositoryGetParentRepository()
    {
        $elementRepository = $this->mandango->getRepository('Model\Element');
        $textElementRepository = $this->mandango->getRepository('Model\TextElement');
        $formElementRepository = $this->mandango->getRepository('Model\FormElement');
        $textareaFormElementRepository = $this->mandango->getRepository('Model\TextareaFormElement');

        $this->assertSame($elementRepository, $textElementRepository->getParentRepository());
        $this->assertSame($elementRepository, $formElementRepository->getParentRepository());
        $this->assertSame($formElementRepository, $textareaFormElementRepository->getParentRepository());
    }

    public function testRepositoryGetLastParentRepository()
    {
        $elementRepository = $this->mandango->getRepository('Model\Element');
        $textElementRepository = $this->mandango->getRepository('Model\TextElement');
        $formElementRepository = $this->mandango->getRepository('Model\FormElement');
        $textareaFormElementRepository = $this->mandango->getRepository('Model\TextareaFormElement');

        $this->assertSame($elementRepository, $textElementRepository->getLastParentRepository());
        $this->assertSame($elementRepository, $formElementRepository->getLastParentRepository());
        $this->assertSame($elementRepository, $textareaFormElementRepository->getLastParentRepository());
    }
}
