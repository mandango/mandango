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

class FilesystemCacheTest extends CacheTestCase
{
    protected function getCacheDriver()
    {
        return new FilesystemCache($this->createTempDir());
    }
}
