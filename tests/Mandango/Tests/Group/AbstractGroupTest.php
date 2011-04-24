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
            new \Model\Article(),
            new \Model\Article(),
            new \Model\Article(),
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
            new \Model\Article(),
            new \Model\Article(),
            new \Model\Article(),
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
        $group->forSaved = array(array('foo' => 'bar'), array('bar' => 'foo'));
        $group->add(array('ups', 'foobar', 'barfoo'));
        $group->remove(array('bar', 'ups'));
        $this->assertSame(3, $group->count());
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
            \Model\Comment::create()->fromArray(array('name' => 'ups')),
            \Model\Comment::create()->fromArray(array('text' => 'foobar')),
        );

        $group = new AbstractGroup('Model\Comment');
        $group->forSaved = $savedData;
        $group->replace($replace);
        $this->assertSame($replace, $group->getAdd());
        $this->assertEquals(array(
            \Model\Comment::create()->fromArray($savedData[0]),
            \Model\Comment::create()->fromArray($savedData[1])
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
