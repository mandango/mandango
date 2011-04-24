<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango;

/**
 * Container of mandangos.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
class Container
{
    static private $mandangos = array();
    static private $defaultName;
    static private $loaders = array();

    /**
     * Set a mandango by name.
     *
     * @param string             $string   The name.
     * @param \Mandango\Mandango $mandango The mandango.
     *
     * @api
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
     *
     * @api
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
     *
     * @api
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
     *
     * @api
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
     *
     * @api
     */
    static public function setDefaultName($name)
    {
        static::$defaultName = $name;
    }

    /**
     * Returns the default name.
     *
     * @return string|null The default name.
     *
     * @api
     */
    static public function getDefaultName()
    {
        return static::$defaultName;
    }

    /**
     * Returns if there is default name.
     *
     * @api
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
     *
     * @api
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
     *
     * @api
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
     *
     * @api
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
     *
     * @api
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
     *
     * @api
     */
    static public function clear()
    {
        static::$mandangos = array();
        static::$defaultName = null;
        static::$loaders = array();
    }
}
