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

use Mandango\Type\IntegerType;

class IntegerTypeTest extends TestCase
{
    public function testToMongo()
    {
        $type = new IntegerType();
        $this->assertSame(123, $type->toMongo('123'));
    }

    public function testToPHP()
    {
        $type = new IntegerType();
        $this->assertSame(123, $type->toPHP('123'));
    }

    public function testToMongoInString()
    {
        $type = new IntegerType();
        $function = $this->getTypeFunction($type->toMongoInString());

        $this->assertSame(123, $function('123'));
    }

    public function testToPHPInString()
    {
        $type = new IntegerType();
        $function = $this->getTypeFunction($type->toPHPInString());

        $this->assertSame(123, $function('123'));
    }
}
