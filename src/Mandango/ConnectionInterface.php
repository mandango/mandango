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
 * ConnectionInterface.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
interface ConnectionInterface
{
    /**
     * Set the logger callable.
     *
     * @param mixed $loggerCallable The logger callable.
     *
     * @throws \RuntimeException When the connection has the Mongo already.
     */
    function setLoggerCallable($loggerCallable = null);

    /**
     * Returns the logger callable.
     *
     * @return mixed The logger callable.
     */
    function getLoggerCallable();

    /**
     * Set the log default.
     *
     * @param array $logDefault The log default.
     *
     * @throws \RuntimeException When the connection has the Mongo already.
     */
    function setLogDefault(array $logDefault);

    /**
     * Returns the log default.
     *
     * @return array|null The log default.
     */
    function getLogDefault();

    /**
     * Returns the mongo connection object.
     *
     * @return \Mongo The mongo collection object.
     */
    function getMongo();

    /**
     * Returns the database object.
     *
     * @return \MongoDB The database object.
     */
    function getMongoDB();
}
