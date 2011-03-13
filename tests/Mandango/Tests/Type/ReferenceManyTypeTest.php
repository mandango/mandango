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

use Mandango\Type\ReferenceManyType;

class ReferenceManyTypeTest extends TestCase
{
    public function testToMongo()
    {
        $type = new ReferenceManyType();

        $ids = array(
            new \MongoId('123'),
            new \MongoId('234'),
            new \MongoId('345'),
        );
        $this->assertSame($ids, $type->toMongo($ids));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider toMongoNotArrayProvider
     */
    public function testToMongoNotArray($value)
    {
        $type = new ReferenceManyType();
        $type->toMongo($value);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      toMongoNotMongoIdProvider
     */
    public function testToMongoNotMongoId($value)
    {
        $type = new ReferenceManyType();
        $type->toMongo($value);
    }

    public function testToPHP()
    {
        $type = new ReferenceManyType();

        $ids = array(
            new \MongoId('123'),
            new \MongoId('234'),
            new \MongoId('345'),
        );
        $this->assertSame($ids, $type->toPHP($ids));
    }

    public function testToMongoInString()
    {
        $type = new ReferenceManyType();
        $function = $this->getTypeFunction($type->toMongoInString());

        $ids = array(
            new \MongoId('123'),
            new \MongoId('234'),
            new \MongoId('345'),
        );
        $this->assertSame($ids, $function($ids));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      toMongoNotArrayProvider
     */
    public function testToMongoInStringNotArray($value)
    {
        $type = new ReferenceManyType();
        $function = $this->getTypeFunction($type->toMongoInString());

        $function($value);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider      toMongoNotMongoIdProvider
     */
    public function testToMongoInStringNotMongoId($value)
    {
        $type = new ReferenceManyType();
        $function = $this->getTypeFunction($type->toMongoInString());

        $function($value);
    }

    public function testToPHPInString()
    {
        $type = new ReferenceManyType();
        $function = $this->getTypeFunction($type->toPHPInString());

        $ids = array(
            new \MongoId('123'),
            new \MongoId('234'),
            new \MongoId('345'),
        );
        $this->assertSame($ids, $function($ids));
    }

    public function toMongoNotArrayProvider()
    {
        return array(
            array(new \MongoId('123')),
            array('string'),
            array(123),
            array(1.23),
            array(new \DateTime()),
        );
    }

    public function toMongoNotMongoIdProvider()
    {
        return array(
            array(array(new \MongoId('123'), 'string')),
            array(array(new \MongoId('123'), 123)),
            array(array(new \MongoId('123'), 1.23)),
            array(array(new \MongoId('123'), array('string'))),
            array(array(new \MongoId('123'), new \DateTime())),
        );
    }
}
