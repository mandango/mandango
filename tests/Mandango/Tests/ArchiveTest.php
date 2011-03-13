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

namespace Mandango\Tests;

use Mandango\Archive;

class ArchiveTest extends TestCase
{
    public function testHasGetSet()
    {
        $object1 = new \DateTime();
        $object2 = new \ArrayObject();

        $this->assertFalse(Archive::has($object1, 'field1'));
        $this->assertFalse(Archive::has($object1, 'field2'));
        $this->assertFalse(Archive::has($object2, 'field3'));

        Archive::set($object1, 'field1', 'foo');
        Archive::set($object1, 'field3', 'bar');
        Archive::set($object2, 'field2', 'ups');
        $this->assertTrue(Archive::has($object1, 'field1'));
        $this->assertFalse(Archive::has($object1, 'field2'));
        $this->assertTrue(Archive::has($object1, 'field3'));
        $this->assertFalse(Archive::has($object2, 'field1'));
        $this->assertTrue(Archive::has($object2, 'field2'));
        $this->assertFalse(Archive::has($object2, 'field3'));

        $this->assertSame('foo', Archive::get($object1, 'field1'));
        $this->assertSame('bar', Archive::get($object1, 'field3'));
        $this->assertSame('ups', Archive::get($object2, 'field2'));

        Archive::remove($object1, 'field1');
        $this->assertFalse(Archive::has($object1, 'field1'));
        $this->assertTrue(Archive::has($object1, 'field3'));
        $this->assertTrue(Archive::has($object2, 'field2'));
    }

    public function testGetByRef()
    {
        $object = new \DateTime();

        $fieldKey =& Archive::getByRef($object, 'field1', array());
        $this->assertSame(array(), $fieldKey);

        $fieldKey['foo'] = 'bar';
        $this->assertSame($fieldKey, Archive::get($object, 'field1'));
    }

    public function testGetOrDefault()
    {
        $object = new \DateTime();

        $this->assertSame('foobar', Archive::getOrDefault($object, 'field1', 'foobar'));

        Archive::set($object, 'field2', 'ups');
        $this->assertSame('ups', Archive::getOrDefault($object, 'field2', 'foobar'));
    }
}
