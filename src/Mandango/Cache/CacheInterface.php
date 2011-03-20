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

namespace Mandango\Cache;

/**
 * CacheInterface.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
interface CacheInterface
{
    /**
     * Checks if the cache has a value for a key.
     *
     * @param string $key A unique key.
     *
     * @return bool Whether the cache has a key.
     */
    function has($key);

    /**
     * Returns the value for a key.
     *
     * @param string $key A unique key.
     *
     * @return mixed The value for a key.
     */
    function get($key);

    /**
     * Sets a value for a key.
     *
     * @param string $key   A unique key.
     * @param mixed  $value The value.
     */
    function set($key, $value);

    /**
     * Removes a value from the cache.
     *
     * @param string $key A unique key.
     */
    function remove($key);

    /**
     * Clears the cache.
     */
    function clear();
}
