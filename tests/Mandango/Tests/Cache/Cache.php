<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
