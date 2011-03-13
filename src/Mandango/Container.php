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
 * Container of mandangos.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class Container
{
    static protected $mandangos = array();
    static protected $defaultName;
    static protected $loaders = array();

    /**
     * Set a mandango by name.
     *
     * @param string             $string   The name.
     * @param \Mandango\Mandango $mandango The mandango.
     */
    static public function set($name, Mandango $mandango)
    {
        static::$mandangos[$name] = $mandango;
    }

    /**
     * Returns a mandango by name.
     *
     * If the name is null the default name is used.
     *
     * @param string|null $name The name (opcional, null by default).
     *
     * @return \Mandango\Mandango A mandango.
     *
     * @throws \RuntimeException If there is not name either default name.
     * @throws \RuntimeException If there is loader and the loader does not return an instance of \Mandango\Mandango.
     * @throws \RuntimeException If there is not Mandango.
     */
    static public function get($name = null)
    {
        // not name
        if (null === $name) {
            // even not default name
            if (null === static::$defaultName) {
                throw new \RuntimeException('There is not name either default name.');
            }

            $name = static::$defaultName;
        }

        // not mandango
        if (!isset(static::$mandangos[$name])) {
            // even not loader
            if (!isset(static::$loaders[$name])) {
                throw new \RuntimeException(sprintf('The mandango "%s" does not exist.', $name));
            }

            // loader
            $mandango = call_user_func(static::$loaders[$name]);
            if (!$mandango instanceof Mandango) {
                throw new \RuntimeException(sprintf('The Mandango "%s" loaded is not an instance of \Mandango\Mandango.', $name));
            }
            static::$mandangos[$name] = $mandango;
        }

        return static::$mandangos[$name];
    }

    /**
     * Returns if a mandango exists.
     *
     * @param string $name The name.
     */
    static public function has($name)
    {
        return isset(static::$mandangos[$name]);
    }

    /**
     * Remove a mandango.
     *
     * @param string $name The name.
     *
     * @throws \InvalidArgumentException If the mandango does not exist.
     */
    static public function remove($name)
    {
        if (!isset(static::$mandangos[$name])) {
            throw new \InvalidArgumentException(sprintf('The mandango "%s" does not exist.', $name));
        }

        unset(static::$mandangos[$name]);
    }

    /**
     * Set the default name.
     *
     * @param string|null $name The default name.
     */
    static public function setDefaultName($name)
    {
        static::$defaultName = $name;
    }

    /**
     * Returns the default name.
     *
     * @return string|null The default name.
     */
    static public function getDefaultName()
    {
        return static::$defaultName;
    }

    /**
     * Returns if there is default name.
     */
    static public function hasDefaultName()
    {
        return null !== static::$defaultName;
    }

    /**
     * Set a loader by name.
     *
     * @param string $name   The name.
     * @param mixed  $loader The loader.
     */
    static public function setLoader($name, $loader)
    {
        static::$loaders[$name] = $loader;
    }

    /**
     * Returns a loader by name.
     *
     * @param string $name The name.
     *
     * @return mixed The loader.
     *
     * @throws \InvalidArgumentException If the loader does not exist.
     */
    static public function getLoader($name)
    {
        if (!isset(static::$loaders[$name])) {
            throw new \InvalidArgumentException(sprintf('The loader "%s" does not exist.', $name));
        }

        return static::$loaders[$name];
    }

    /**
     * Returns if a loader exists.
     *
     * @param string $name The name.
     */
    static public function hasLoader($name)
    {
        return isset(static::$loaders[$name]);
    }

    /**
     * Remove a loader.
     *
     * @param string $name The name.
     *
     * @throws \InvalidArgumentException If the loader does not exist
     */
    static public function removeLoader($name)
    {
        if (!isset(static::$loaders[$name])) {
            throw new \InvalidArgumentException(sprintf('The loader "%s" does not exist.', $name));
        }

        unset(static::$loaders[$name]);
    }

    /**
     * Clear the mandangos, default name and loaders.
     */
    static public function clear()
    {
        static::$mandangos = array();
        static::$defaultName = null;
        static::$loaders = array();
    }
}
