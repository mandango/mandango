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

use Mandango\Type\BooleanType;

class BooleanTypeTest extends TestCase
{
    public function testToMongo()
    {
        $type = new BooleanType();
        $this->assertTrue($type->toMongo(1));
        $this->assertFalse($type->toMongo(0));
    }

    public function testToPHP()
    {
        $type = new BooleanType();
        $this->assertTrue($type->toPHP(1));
        $this->assertFalse($type->toPHP(0));
    }

    public function testToMongoInString()
    {
        $type = new BooleanType();
        $function = $this->getTypeFunction($type->toMongoInString());

        $this->assertTrue($function(1));
        $this->assertFalse($function(0));
    }

    public function testToPHPInString()
    {
        $type = new BooleanType();
        $function = $this->getTypeFunction($type->toPHPInString());

        $this->assertTrue($function(1));
        $this->assertFalse($function(0));
    }
}
