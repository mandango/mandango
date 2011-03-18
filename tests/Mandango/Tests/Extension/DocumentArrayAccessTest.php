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

namespace Mandango\Tests\Extension;

use Mandango\Tests\TestCase;

class DocumentArrayAccessTest extends TestCase
{
    /**
     * @expectedException \LogicException
     */
    public function testOffsetExists()
    {
        $article = new \Model\Article();
        isset($article['title']);
    }

    public function testOffsetSet()
    {
        $article = new \Model\Article();
        $article['title'] = 'foo';
        $this->assertSame('foo', $article->getTitle());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOffsetSetNameNotExists()
    {
        $article = new \Model\Article();
        $article['no'] = 'foo';
    }

    public function testOffsetGet()
    {
        $article = new \Model\Article();
        $article->setTitle('bar');
        $this->assertSame('bar', $article['title']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOffsetGetNameNotExists()
    {
        $article = new \Model\Article();
        $article['no'];
    }

    /**
     * @expectedException \LogicException
     */
    public function testOffsetUnset()
    {
        $article = new \Model\Article();
        unset($article['title']);
    }
}
