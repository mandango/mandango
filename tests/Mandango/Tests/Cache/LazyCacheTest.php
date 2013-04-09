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

use Mandango\Cache\FilesystemCache;
use Mandango\Cache\LazyCache;

class LazyCacheTest extends CacheTestCase
{
    private $delegateMock;
    private $lazyCache;

    protected function setUp()
    {
        parent::setUp();

        $this->delegateMock = $this->getMock('Mandango\Cache\CacheInterface');
        $this->lazyCache = new LazyCache($this->delegateMock);
    }

    protected function getCacheDriver()
    {
        $delegate = new FilesystemCache($this->createTempDir());

        return new LazyCache($delegate);
    }

    public function testHasShouldReturnTrueDependingOnDelegate()
    {
        $key = 'foo';
        $value = 'bar';

        $this->delegateMock->expects($this->any())
                           ->method('get')
                           ->with($key)
                           ->will($this->returnValue($value));

        $this->assertTrue($this->lazyCache->has($key));
    }

    public function testHasShouldReturnFalseDependingOnDelegate()
    {
        $key = 'foo';
        $value = null;

        $this->delegateMock->expects($this->any())
                           ->method('get')
                           ->with($key)
                           ->will($this->returnValue($value));

        $this->assertFalse($this->lazyCache->has($key));
    }

    public function testGetShouldReturnNotNullDependingOnDelegate()
    {
        $key = 'foo';
        $value = 'bar';

        $this->delegateMock->expects($this->any())
                           ->method('get')
                           ->with($key)
                           ->will($this->returnValue($value));

        $this->assertSame($value, $this->lazyCache->get($key));
    }

    public function testGetShouldReturnNullDependingOnDelegate()
    {
        $key = 'foo';
        $value = null;

        $this->delegateMock->expects($this->any())
                           ->method('get')
                           ->with($key)
                           ->will($this->returnValue($value));

        $this->assertNull($this->lazyCache->get($key));
    }

    public function testSetShouldCallDelegateSet()
    {
        $key = 'foo';
        $value = 'bar';

        $this->delegateMock->expects($this->once())
                           ->method('set')
                           ->with($key, $value);

        $this->lazyCache->set($key, $value);
    }

    public function testRemoveShouldCallDelegateRemove()
    {
        $key = 'foo';

        $this->delegateMock->expects($this->once())
                           ->method('remove')
                           ->with($key);

        $this->lazyCache->remove($key);
    }

    public function testClearShouldCallDelegateClear()
    {
        $this->delegateMock->expects($this->once())
                           ->method('clear');

        $this->lazyCache->clear();
    }
}
