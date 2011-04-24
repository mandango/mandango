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
 * MandangoInterface.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
interface MandangoInterface
{
    /**
     * Returns the metadata.
     *
     * @return Metadata The metadata.
     *
     * @api
     */
    function getMetadata();

    /**
     * Returns the query cache.
     *
     * @return CacheInterface The query cache.
     *
     * @api
     */
    function getQueryCache();

    /**
     * Returns the logger callable.
     *
     * @return mixed The logger callable.
     *
     * @api
     */
    function getLoggerCallable();

    /**
     * Returns the UnitOfWork.
     *
     * @return UnitOfWork The UnitOfWork.
     *
     * @api
     */
    function getUnitOfWork();

    /**
     * Set a connection.
     *
     * @param string              $name       The connection name.
     * @param ConnectionInterface $connection The connection.
     *
     * @api
     */
    function setConnection($name, ConnectionInterface $connection);

    /**
     * Set the connections.
     *
     * @param array $connections An array of connections.
     *
     * @api
     */
    function setConnections(array $connections);

    /**
     * Remove a connection.
     *
     * @param string $name The connection name.
     *
     * @throws \InvalidArgumentException If the connection does not exists.
     *
     * @api
     */
    function removeConnection($name);

    /**
     * Clear the connections.
     *
     * @api
     */
    function clearConnections();

    /**
     * Returns if a connection exists.
     *
     * @param string $name The connection name.
     *
     * @return boolean Returns if a connection exists.
     *
     * @api
     */
    function hasConnection($name);

    /**
     * Return a connection.
     *
     * @param string $name The connection name.
     *
     * @return ConnectionInterface The connection.
     *
     * @throws \InvalidArgumentException If the connection does not exists.
     *
     * @api
     */
    function getConnection($name);

    /**
     * Returns the connections.
     *
     * @return array The array of connections.
     *
     * @api
     */
    function getConnections();

    /**
     * Set the default connection name.
     *
     * @param string $name The connection name.
     *
     * @api
     */
    function setDefaultConnectionName($name);

    /**
     * Returns the default connection name.
     *
     * @return string The default connection name.
     *
     * @api
     */
    function getDefaultConnectionName();

    /**
     * Returns the default connection.
     *
     * @return ConnectionInterface The default connection.
     *
     * @throws \RuntimeException If there is not default connection name.
     * @throws \RuntimeException If the default connection does not exists.
     *
     * @api
     */
    function getDefaultConnection();

    /**
     * Returns repositories by document class.
     *
     * @param string $documentClass The document class.
     *
     * @return Mandango\Repository The repository.
     *
     * @throws \InvalidArgumentException If the document class is not a valid document class.
     * @throws \RuntimeException         If the repository class build does not exist.
     *
     * @api
     */
    function getRepository($documentClass);

    /**
     * Returns all repositories.
     *
     * @return array All repositories.
     *
     * @api
     */
    function getAllRepositories();

    /**
     * Ensure the indexes of all repositories.
     *
     * @api
     */
    function ensureAllIndexes();

    /**
     * Access to UnitOfWork ->persist() method.
     *
     * @see UnitOfWork::persist()
     *
     * @api
     */
    function persist($documents);

    /**
     * Access to UnitOfWork ->remove() method.
     *
     * @see Mandango\UnitOfWork::remove()
     *
     * @api
     */
    function remove($documents);

    /**
     * Access to UnitOfWork ->commit() method.
     *
     * @see Mandango\UnitOfWork::commit()
     *
     * @api
     */
    function flush();
}
