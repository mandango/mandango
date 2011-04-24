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

use Mandango\Type\FloatType;

class FloatTypeTest extends TestCase
{
    public function testToMongo()
    {
        $type = new FloatType();
        $this->assertSame(123.45, $type->toMongo('123.45'));
    }

    public function testToPHP()
    {
        $type = new FloatType();
        $this->assertSame(123.45, $type->toPHP('123.45'));
    }

    public function testToMongoInString()
    {
        $type = new FloatType();
        $function = $this->getTypeFunction($type->toMongoInString());

        $this->assertSame(123.45, $function('123.45'));
    }

    public function testToPHPInString()
    {
        $type = new FloatType();
        $function = $this->getTypeFunction($type->toPHPInString());

        $this->assertSame(123.45, $function('123.45'));
    }
}
