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
 * Base class for Mandango types.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
abstract class Type
{
    /**
     * Convert a PHP value to a Mongo value.
     *
     * @param mixed $value The PHP value.
     *
     * @return mixed The Mongo value.
     */
    abstract public function toMongo($value);

    /**
     * Convert a Mongo value to a PHP value.
     *
     * @param mixed $value The Mongo value.
     *
     * @return mixed The PHP value.
     */
    abstract public function toPHP($value);

    /**
     * Convert a PHP value to a Mongo value (in string).
     *
     * @return string Code to convert the value.
     */
    abstract public function toMongoInString();

    /**
     * Convert a Mongo value to a PHP value (in string).
     *
     * @return mixed Code to conver the value.
     */
    abstract public function toPHPInString();
}
