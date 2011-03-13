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

namespace Mandango\Tests\Type;

use Mandango\Type\ReferenceOneType;

class ReferenceOneTypeTest extends TestCase
{
    public function testToMongo()
    {
        $type = new ReferenceOneType();

        $id = new \MongoId('123');
        $this->assertSame($id, $type->toMongo($id));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      toMongoProvider
     */
    public function testToMongoNotMongoId($value)
    {
        $type = new ReferenceOneType();
        $type->toMongo($value);
    }

    public function testToPHP()
    {
        $type = new ReferenceOneType();

        $id = new \MongoId('123');
        $this->assertSame($id, $type->toPHP($id));
    }

    public function testToMongoInString()
    {
        $type = new ReferenceOneType();
        $function = $this->getTypeFunction($type->toMongoInString());

        $id = new \MongoId('123');
        $this->assertSame($id, $function($id));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      toMongoProvider
     */
    public function testToMongoInStringNotMongoId($value)
    {
        $type = new ReferenceOneType();
        $function = $this->getTypeFunction($type->toMongoInString());

        $function($value);
    }

    public function testToPHPInString()
    {
        $type = new ReferenceOneType();
        $function = $this->getTypeFunction($type->toPHPInString());

        $id = new \MongoId('123');
        $this->assertSame($id, $function($id));
    }

    public function toMongoProvider()
    {
        return array(
            array('string'),
            array(123),
            array(1.23),
            array(array('string')),
            array(new \DateTime()),
        );
    }
}
