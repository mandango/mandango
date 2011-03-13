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

namespace Mandango;

/**
 * Archive to save things related to objects.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class Archive
{
    static protected $archive = array();

    /**
     * Returns if an object has a key in the archive.
     *
     * @param object $object The object.
     * @param string $key    The key.
     *
     * ®return bool If an object has a key in the archive.
     */
    static public function has($object, $key)
    {
        $oid = spl_object_hash($object);

        return isset(static::$archive[$oid]) && (isset(static::$archive[$oid][$key]) || array_key_exists($key, static::$archive[$oid]));
    }

    /**
     * Returns the value of an object key.
     *
     * It does not check if the object key exists, if you want to check it, do by yourself.
     *
     * @param object $object The object.
     * @param string $key    The key.
     *
     * @return mixed The value of the object key.
     */
    static public function get($object, $key)
    {
        return static::$archive[spl_object_hash($object)][$key];
    }

    /**
     * Set an object key value.
     *
     * @param object $object The object.
     * @param string $key    The key.
     * @param mixed  $value  The value.
     */
    static public function set($object, $key, $value)
    {
        static::$archive[spl_object_hash($object)][$key] = $value;
    }

    /**
     * Remove an object key.
     *
     * @param object $object The object.
     * @param string $key    The key.
     */
    static public function remove($object, $key)
    {
        unset(static::$archive[spl_object_hash($object)][$key]);
    }

    /**
     * Returns an object key by reference. It creates the key if the key does not exist.
     *
     * @param object $object  The object
     * @param string $key     The key.
     * @param mixed  $default The default value, used to create the key if it does not exist (null by default).
     *
     * @return mixed The object key value.
     */
    static public function &getByRef($object, $key, $default = null)
    {
        $oid = spl_object_hash($object);

        if (!isset(static::$archive[$oid][$key])) {
            static::$archive[$oid][$key] = $default;
        }

        return static::$archive[$oid][$key];
    }

    /**
     * Returns an object key or returns a default value otherwise.
     *
     * @param object $object  The object.
     * @param string $key     The key.
     * @param mixed  $default The value to return if the object key does not exist.
     *
     * @return mixed The object key value or the default value.
     */
    static public function getOrDefault($object, $key, $default)
    {
        $oid = spl_object_hash($object);

        if (isset(static::$archive[$oid]) && (isset(static::$archive[$oid][$key]) || array_key_exists($key, static::$archive[$oid]))) {
            return static::$archive[$oid][$key];
        }

        return $default;
    }

    /**
     * Returns all objects data.
     *
     * @return array All objects data.
     */
    static public function all()
    {
        return static::$archive;
    }

    /**
     * Clear all objects data.
     */
    static public function clear()
    {
        static::$archive = array();
    }

    /**
     * Returns if an object exist in the archive.
     *
     * @param object $object The object.
     *
     * @return bool If the object exists in the archive.
     */
    static public function hasObject($object)
    {
        return isset(static::$archive[spl_object_hash($object)]);
    }

    /**
     * Returns the object data in the archive.
     *
     * @param object $object The object.
     *
     * @return array The object data in the archive.
     */
    static public function getObject($object)
    {
        return static::$archive[spl_object_hash($object)];
    }

    /**
     * Remove the object data in the archive.
     *
     * @param object $object The object.
     */
    static public function removeObject($object)
    {
        unset(static::$archive[spl_object_hash($object)]);
    }
}
