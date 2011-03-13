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
