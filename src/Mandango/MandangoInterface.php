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
 */
interface MandangoInterface
{
    /**
     * Returns the metadata.
     *
     * @return Metadata The metadata.
     */
    function getMetadata();

    /**
     * Returns the query cache.
     *
     * @return CacheInterface The query cache.
     */
    function getQueryCache();

    /**
     * Returns the logger callable.
     *
     * @return mixed The logger callable.
     */
    function getLoggerCallable();

    /**
     * Returns the UnitOfWork.
     *
     * @return UnitOfWork The UnitOfWork.
     */
    function getUnitOfWork();

    /**
     * Set a connection.
     *
     * @param string              $name       The connection name.
     * @param ConnectionInterface $connection The connection.
     */
    function setConnection($name, ConnectionInterface $connection);

    /**
     * Set the connections.
     *
     * @param array $connections An array of connections.
     */
    function setConnections(array $connections);

    /**
     * Remove a connection.
     *
     * @param string $name The connection name.
     *
     * @throws \InvalidArgumentException If the connection does not exists.
     */
    function removeConnection($name);

    /**
     * Clear the connections.
     */
    function clearConnections();

    /**
     * Returns if a connection exists.
     *
     * @param string $name The connection name.
     *
     * @return boolean Returns if a connection exists.
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
     */
    function getConnection($name);

    /**
     * Returns the connections.
     *
     * @return array The array of connections.
     */
    function getConnections();

    /**
     * Set the default connection name.
     *
     * @param string $name The connection name.
     */
    function setDefaultConnectionName($name);

    /**
     * Returns the default connection name.
     *
     * @return string The default connection name.
     */
    function getDefaultConnectionName();

    /**
     * Returns the default connection.
     *
     * @return ConnectionInterface The default connection.
     *
     * @throws \RuntimeException If there is not default connection name.
     * @throws \RuntimeException If the default connection does not exists.
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
     */
    function getRepository($documentClass);

    /**
     * Returns all repositories.
     *
     * @return array All repositories.
     */
    function getAllRepositories();

    /**
     * Ensure the indexes of all repositories.
     */
    function ensureAllIndexes();

    /**
     * Access to UnitOfWork ->persist() method.
     *
     * @see UnitOfWork::persist()
     */
    function persist($documents);

    /**
     * Access to UnitOfWork ->remove() method.
     *
     * @see Mandango\UnitOfWork::remove()
     */
    function remove($documents);

    /**
     * Access to UnitOfWork ->commit() method.
     *
     * @see Mandango\UnitOfWork::commit()
     */
    function flush();
}
