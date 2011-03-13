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

use Mandango\Type\DateType;

class DateTypeTest extends TestCase
{
    public function testToMongo()
    {
        $type = new DateType();

        $time = time();
        $this->assertEquals(new \MongoDate($time), $type->toMongo($time));

        $date = new \DateTime();
        $date->setTimestamp($time);
        $this->assertEquals(new \MongoDate($time), $type->toMongo($date));

        $string = '2010-02-20';
        $this->assertEquals(new \MongoDate(strtotime($string)), $type->toMongo($string));
    }

    public function testToPHP()
    {
        $type = new DateType();

        $time = time();
        $date = new \DateTime();
        $date->setTimestamp($time);

        $this->assertEquals($date, $type->toPHP(new \MongoDate($time)));
    }

    public function testToMongoInString()
    {
        $type = new DateType();
        $function = $this->getTypeFunction($type->toMongoInString());

        $time = time();
        $this->assertEquals(new \MongoDate($time), $function($time));

        $date = new \DateTime();
        $date->setTimestamp($time);
        $this->assertEquals(new \MongoDate($time), $function($date));

        $string = '2010-02-20';
        $this->assertEquals(new \MongoDate(strtotime($string)), $function($string));
    }

    public function testToPHPInString()
    {
        $type = new DateType();
        $function = $this->getTypeFunction($type->toPHPInString());

        $time = time();
        $date = new \DateTime();
        $date->setTimestamp($time);

        $this->assertEquals($date, $function(new \MongoDate($time)));
    }
}
