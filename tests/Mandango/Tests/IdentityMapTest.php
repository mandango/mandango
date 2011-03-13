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

namespace Mandango\Tests;

use Mandango\IdentityMap;

class IdentityMapTest extends TestCase
{
    public function testGeneral()
    {
        $articles = array();
        for ($i = 1; $i <= 10; $i ++) {
            $articles[$i] = \Model\Article::create()->setId(new \MongoId($i));
        }

        $identityMap = new IdentityMap();

        // set
        $identityMap->set($articles[1]->getId(), $articles[1]);
        $identityMap->set($articles[2]->getId(), $articles[2]);

        // has
        $this->assertTrue($identityMap->has($articles[1]->getId()));
        $this->assertTrue($identityMap->has($articles[2]->getId()));
        $this->assertFalse($identityMap->has($articles[3]->getId()));
        $this->assertFalse($identityMap->has($articles[4]->getId()));

        // get
        $this->assertSame($articles[1], $identityMap->get($articles[1]->getId()));
        $this->assertSame($articles[2], $identityMap->get($articles[2]->getId()));

        // set overwriting
        $identityMap->set($articles[2]->getId(), $articles[3]);
        $this->assertSame($articles[3], $identityMap->get($articles[2]->getId()));
        $identityMap->set($articles[2]->getId(), $articles[2]);

        // all
        $this->assertSame(array(
            $articles[1]->getId()->__toString() => $articles[1],
            $articles[2]->getId()->__toString() => $articles[2],
        ), $identityMap->all());

        // remove
        $identityMap->set($articles[4]->getId(), $articles[4]);
        $identityMap->set($articles[5]->getId(), $articles[5]);
        $identityMap->remove($articles[4]->getId());
        $this->assertSame(array(
            $articles[1]->getId()->__toString() => $articles[1],
            $articles[2]->getId()->__toString() => $articles[2],
            $articles[5]->getId()->__toString() => $articles[5],
        ), $identityMap->all());

        // clear
        $identityMap->clear();
        $this->assertSame(array(), $identityMap->all());
    }
}
