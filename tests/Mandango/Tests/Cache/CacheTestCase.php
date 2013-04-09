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

abstract class CacheTestCase extends TestCase
{
    protected $cache;

    protected function setUp()
    {
        $this->cache = $this->getCacheDriver();
    }

    abstract protected function getCacheDriver();

    public function testHasShouldReturnTrueIfTheKeyExists()
    {
        $this->cache->set('foo', 'bar');
        $this->assertTrue($this->cache->has('foo'));
    }

    public function testHasShouldReturnFalseIfTheKeyDoesNotExists()
    {
        $this->cache->set('foo', 'bar');
        $this->assertFalse($this->cache->has('ups'));
    }

    public function testGetShouldReturnTheValueOfTheKey()
    {
        $this->cache->set('foo', 'bar');
        $this->cache->set('ups', 'man');

        $this->assertSame('bar', $this->cache->get('foo'));
        $this->assertSame('man', $this->cache->get('ups'));
    }

    public function testGetShouldReturnNullIfTheKeyDoesNotExist()
    {
        $this->cache->set('foo', 'bar');

        $this->assertNull($this->cache->get('ups'));
    }

    public function testRemoveShouldRemoveAKey()
    {
        $this->cache->set('ups', 'man');
        $this->cache->remove('ups');

        $this->assertFalse($this->cache->has('ups'));
        $this->assertNull($this->cache->get('ups'));
    }

    public function testRemoveShouldRemoveOnlyOneKey()
    {
        $this->cache->set('foo', 'bar');
        $this->cache->set('ups', 'man');
        $this->cache->remove('ups');

        $this->assertTrue($this->cache->has('foo'));
        $this->assertSame('bar', $this->cache->get('foo'));
    }

    public function testClearShouldRemoveAllKeys()
    {
        $this->cache->set('foo', 'bar');
        $this->cache->set('ups', 'man');
        $this->cache->clear();

        $this->assertFalse($this->cache->has('foo'));
        $this->assertFalse($this->cache->has('ups'));
    }

    protected function createTempDir()
    {
        return sys_get_temp_dir().
               '/mandango_filesystem_cache_tests'.
               mt_rand(111111, 999999);
    }
}
