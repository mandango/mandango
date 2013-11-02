<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Tests;

use Mandango\IdentityMap;

class IdentityMapTest extends TestCase
{
    public function testGeneral()
    {
        $articles = array();
        for ($i = 1; $i <= 10; $i ++) {
            $articles[$i] = $this->mandango->create('Model\Article')->setId(new \MongoId($this->generateObjectId()));
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
