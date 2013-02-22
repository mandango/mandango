<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Tests\Group;

use Mandango\Tests\TestCase;
use Mandango\Group\AbstractGroup as BaseAbstractGroup;

class AbstractGroup extends BaseAbstractGroup
{
    public $forSaved = array();

    protected function doInitializeSavedData()
    {
        return $this->forSaved;
    }
}

class AbstractGroupTest extends TestCase
{
    public function testAdd()
    {
        $add = array(
            $this->mandango->create('Model\Article'),
            $this->mandango->create('Model\Article'),
            $this->mandango->create('Model\Article'),
        );

        $group = new AbstractGroup('Model\Comment');
        $group->add($add[0]);
        $this->assertSame(array($add[0]), $group->getAdd());
        $group->add(array($add[1], $add[2]));
        $this->assertSame($add, $group->getAdd());
        $group->clearAdd();
        $this->assertSame(array(), $group->getAdd());
    }

    public function testRemove()
    {
        $remove = array(
            $this->mandango->create('Model\Article'),
            $this->mandango->create('Model\Article'),
            $this->mandango->create('Model\Article'),
        );

        $group = new AbstractGroup('Model\Comment');
        $group->remove($remove[0]);
        $this->assertSame(array($remove[0]), $group->getRemove());
        $group->remove(array($remove[1], $remove[2]));
        $this->assertSame($remove, $group->getRemove());
        $group->clearRemove();
        $this->assertSame(array(), $group->getRemove());
    }

    public function testSaved()
    {
        $forSaved1 = array('foo', 'bar');
        $forSaved2 = array('bar', 'foo');

        $group = new AbstractGroup('Model\Comment');
        $this->assertFalse($group->isSavedInitialized());
        $group->forSaved = $forSaved1;
        $this->assertSame($forSaved1, $group->getSaved());
        $this->assertTrue($group->isSavedInitialized());
        $group->forSaved = $forSaved2;
        $this->assertSame($forSaved1, $group->getSaved());
        $group->refreshSaved();
        $this->assertSame($forSaved2, $group->getSaved());
        $group->clearSaved();
        $this->assertFalse($group->isSavedInitialized());
    }

    public function testAll()
    {
        $group = new AbstractGroup('Model\Comment');
        $group->forSaved = array('foo', 'bar', 'foobar', 'barfoo');
        $group->add(array('ups', 'spu'));
        $group->remove(array('bar', 'spu'));

        $this->assertSame(array('foo', 'foobar', 'barfoo', 'ups'), $group->all());
    }

    public function testAllShouldDoNothingWithNotExistingDocuments()
    {
        $group = new AbstractGroup('Model\Comment');
        $group->forSaved = array('foo', 'bar');
        $group->remove(array('ups'));

        $this->assertSame(array('foo', 'bar'), $group->all());
    }

    public function testSavedIteratorAggregateInterface()
    {
        $forSaved = array('foo', 'bar');

        $group = new AbstractGroup('Model\Comment');
        $group->forSaved = $forSaved;
        $group->add(array('ups', 'foobar'));
        $group->remove(array('bar', 'ups'));
        $this->assertSame(array('foo', 'foobar'), iterator_to_array($group));
    }

    public function testCount()
    {
        $group = new AbstractGroup('Model\Comment');
        $group->forSaved = array('foo', 'bar');
        $group->add(array('ups'));
        $group->remove(array('bar'));
        $this->assertSame(2, $group->count());
    }

    public function testCountableInterface()
    {
        $group = new AbstractGroup('Model\Comment');
        $group->forSaved = array(array('foo' => 'bar'), array('bar' => 'foo'));
        $this->assertSame(2, count($group));
    }

    public function replace()
    {
        $savedData = array(
            array('name' => 'foo'),
            array('name' => 'bar'),
        );
        $replace = array(
            $this->mandango->create('Model\Comment')->fromArray(array('name' => 'ups')),
            $this->mandango->create('Model\Comment')->fromArray(array('text' => 'foobar')),
        );

        $group = new AbstractGroup('Model\Comment');
        $group->forSaved = $savedData;
        $group->replace($replace);
        $this->assertSame($replace, $group->getAdd());
        $this->assertEquals(array(
            $this->mandango->create('Model\Comment')->fromArray($savedData[0]),
            $this->mandango->create('Model\Comment')->fromArray($savedData[1])
        ), $group->getRemove());
    }

    public function testResetWithAdd()
    {
        $group = new AbstractGroup();
        $group->add('foo');
        $group->reset();
        $this->assertSame(array(), $group->getAdd());
        $this->assertSame(array(), $group->getRemove());
        $this->assertSame(array(), $group->getSaved());
    }

    public function testResetWithRemove()
    {
        $group = new AbstractGroup();
        $group->remove('foo');
        $group->reset();
        $this->assertSame(array(), $group->getAdd());
        $this->assertSame(array(), $group->getRemove());
        $this->assertSame(array(), $group->getSaved());
    }

    public function testResetSavedWithAdd()
    {
        $group = new AbstractGroup();
        $group->forSaved = array('foo');
        $group->getSaved();
        $group->forSaved = array('foobar');
        $group->add('bar');
        $group->reset();
        $this->assertSame(array(), $group->getAdd());
        $this->assertSame(array(), $group->getRemove());
        $this->assertSame(array('foobar'), $group->getSaved());
    }

    public function testResetSavedWithRemove()
    {
        $group = new AbstractGroup();
        $group->forSaved = array('foo');
        $group->getSaved();
        $group->forSaved = array('foobar');
        $group->remove('bar');
        $group->reset();
        $this->assertSame(array(), $group->getAdd());
        $this->assertSame(array(), $group->getRemove());
        $this->assertSame(array('foobar'), $group->getSaved());
    }

    public function testResetNoSavedWithoutAddNorRemove()
    {
        $group = new AbstractGroup();
        $group->forSaved = array('foo');
        $group->getSaved();
        $group->forSaved = array('foobar');
        $group->reset();
        $this->assertSame(array(), $group->getAdd());
        $this->assertSame(array(), $group->getRemove());
        $this->assertSame(array('foo'), $group->getSaved());
    }
}
