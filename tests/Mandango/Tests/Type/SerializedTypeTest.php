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

use Mandango\Type\SerializedType;

class SerializedTypeTest extends TestCase
{
    protected $array = array('foo' => 'bar');

    public function testToMongo()
    {
        $type = new SerializedType();
        $this->assertSame(serialize($this->array), $type->toMongo($this->array));
    }

    public function testToPHP()
    {
        $type = new SerializedType();
        $this->assertSame($this->array, $type->toPHP(serialize($this->array)));
    }

    public function testToMongoInString()
    {
        $type = new SerializedType();
        $function = $this->getTypeFunction($type->toMongoInString());

        $this->assertSame(serialize($this->array), $function($this->array));
    }

    public function testToPHPInString()
    {
        $type = new SerializedType();
        $function = $this->getTypeFunction($type->toPHPInString());

        $this->assertSame($this->array, $function(serialize($this->array)));
    }
}
