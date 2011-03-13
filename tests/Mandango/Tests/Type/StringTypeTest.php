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

use Mandango\Type\StringType;

class StringTypeTest extends TestCase
{
    public function testToMongo()
    {
        $type = new StringType();
        $this->assertSame('123', $type->toMongo(123));
    }

    public function testToPHP()
    {
        $type = new StringType();
        $this->assertSame('123', $type->toPHP(123));
    }

    public function testToMongoInString()
    {
        $type = new StringType();
        $function = $this->getTypeFunction($type->toMongoInString());

        $this->assertSame('123', $function(123));
    }

    public function testToPHPInString()
    {
        $type = new StringType();
        $function = $this->getTypeFunction($type->toPHPInString());

        $this->assertSame('123', $function(123));
    }
}
