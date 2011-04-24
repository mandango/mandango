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

use Mandango\Type\BinDataType;

class BinDataTypeTest extends TestCase
{
    public function testToMongo()
    {
        $type = new BinDataType();
        $this->assertEquals(new \MongoBinData('123'), $type->toMongo('123'));
    }

    public function testToPHP()
    {
        $type = new BinDataType();
        $this->assertSame('123', $type->toPHP(new \MongoBinData('123')));
    }

    public function testToMongoInString()
    {
        $type = new BinDataType();
        $function = $this->getTypeFunction($type->toMongoInString());

        $this->assertEquals(new \MongoBinData('123'), $function('123'));
    }

    public function testToPHPInString()
    {
        $type = new BinDataType();
        $function = $this->getTypeFunction($type->toPHPInString());

        $this->assertSame('123', $function(new \MongoBinData('123')));
    }
}
