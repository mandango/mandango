<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
