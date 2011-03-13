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

namespace Mandango\Type;

/**
 * Container of types.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class Container
{
    static protected $map = array(
        'bin_data'       => 'Mandango\Type\BinDataType',
        'boolean'        => 'Mandango\Type\BooleanType',
        'date'           => 'Mandango\Type\DateType',
        'float'          => 'Mandango\Type\FloatType',
        'integer'        => 'Mandango\Type\IntegerType',
        'raw'            => 'Mandango\Type\RawType',
        'reference_one'  => 'Mandango\Type\ReferenceOneType',
        'reference_many' => 'Mandango\Type\ReferenceManyType',
        'serialized'     => 'Mandango\Type\SerializedType',
        'string'         => 'Mandango\Type\StringType',
    );

    static protected $types = array();

    /**
     * Returns if exists a type by name.
     *
     * @param string $name The type name.
     *
     * @return bool Returns if the type exists.
     */
    static public function has($name)
    {
        return isset(static::$map[$name]);
    }

    /**
     * Add a type.
     *
     * @param string $name  The type name.
     * @param string $class The type class.
     *
     * @throws \InvalidArgumentException If the type already exists.
     * @throws \InvalidArgumentException If the class is not a subclass of Mandango\Type\Type.
     */
    static public function add($name, $class)
    {
        if (static::has($name)) {
            throw new \InvalidArgumentException(sprintf('The type "%s" already exists.', $name));
        }

        $r = new \ReflectionClass($class);
        if (!$r->isSubclassOf('Mandango\Type\Type')) {
            throw new \InvalidArgumentException(sprintf('The class "%s" is not a subclass of Mandango\Type\Type.', $class));
        }

        static::$map[$name] = $class;
    }

    /**
     * Returns a type.
     *
     * @param string $name The type name.
     *
     * @return Mandango\Type\Type The type.
     *
     * @throws \InvalidArgumentException If the type does not exists.
     */
    static public function get($name)
    {
        if (!isset(static::$types[$name])) {
            if (!static::has($name)) {
                throw new \InvalidArgumentException(sprintf('The type "%s" does not exists.', $name));
            }

            static::$types[$name] = new static::$map[$name];
        }

        return static::$types[$name];
    }

    /**
     * Remove a type.
     *
     * @param string $name The type name.
     *
     * @throws \InvalidArgumentException If the type does not exists.
     */
    static public function remove($name)
    {
        if (!static::has($name)) {
            throw new \InvalidArgumentException(sprintf('The type "%s" does not exists.', $name));
        }

        unset(static::$map[$name], static::$types[$name]);
    }

    /**
     * Reset the types.
     */
    static public function reset()
    {
        static::$map = array(
            'bin_data'       => 'Mandango\Type\BinDataType',
            'boolean'        => 'Mandango\Type\BooleanType',
            'date'           => 'Mandango\Type\DateType',
            'float'          => 'Mandango\Type\FloatType',
            'integer'        => 'Mandango\Type\IntegerType',
            'raw'            => 'Mandango\Type\RawType',
            'reference_one'  => 'Mandango\Type\ReferenceOneType',
            'reference_many' => 'Mandango\Type\ReferenceManyType',
            'serialized'     => 'Mandango\Type\SerializedType',
            'string'         => 'Mandango\Type\StringType',
        );

        static::$types = array();
    }
}
