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

namespace Mandango\Tests\Cache;

use Mandango\Tests\TestCase;

abstract class Cache extends TestCase
{
    protected $cache;

    protected function setUp()
    {
        $this->cache = $this->getCacheDriver();
    }

    public function testCache()
    {
        $key1 = 'foo';
        $key2 = 'bar';
        $value1 = 'ups';
        $value2 = 'ngo';

        $this->assertFalse($this->cache->has($key1));
        $this->assertFalse($this->cache->has($key2));

        $this->cache->set($key1, $value1);
        $this->assertTrue($this->cache->has($key1));
        $this->assertFalse($this->cache->has($key2));
        $this->assertSame($value1, $this->cache->get($key1));
        $this->assertNull($this->cache->get($key2));

        $this->cache->set($key2, $value2);
        $this->assertTrue($this->cache->has($key1));
        $this->assertTrue($this->cache->has($key2));
        $this->assertSame($value1, $this->cache->get($key1));
        $this->assertSame($value2, $this->cache->get($key2));

        $this->cache->remove($key1);
        $this->assertFalse($this->cache->has($key1));
        $this->assertTrue($this->cache->has($key2));
        $this->assertNull($this->cache->get($key1));
        $this->assertSame($value2, $this->cache->get($key2));

        $this->cache->clear();
        $this->assertFalse($this->cache->has($key1));
        $this->assertFalse($this->cache->has($key2));
    }

    abstract protected function getCacheDriver();
}
