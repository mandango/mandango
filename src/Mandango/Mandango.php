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

use Mandango\Cache\CacheInterface;

/**
 * Mandango.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class Mandango
{
    const VERSION = '1.0.0-DEV';

    protected $metadata;
    protected $queryCache;
    protected $loggerCallable;
    protected $unitOfWork;
    protected $connections = array();
    protected $defaultConnectionName;
    protected $repositories = array();

    /**
     * Constructor.
     *
     * @param Mandango\Metadata             $metadata       The metadata.
     * @param Mandango\Cache\CacheInterface $queryCache     The query cache.
     * @param mixed                         $loggerCallable The logger callable (optional, null by default).
     */
    public function __construct(Metadata $metadata, CacheInterface $queryCache, $loggerCallable = null)
    {
        $this->metadata = $metadata;
        $this->queryCache = $queryCache;
        $this->loggerCallable = $loggerCallable;
        $this->unitOfWork = new UnitOfWork($this);
    }

    /**
     * Returns the metadata.
     *
     * @return Mandango\Metadata The metadata.
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Returns the query cache.
     *
     * @return Mandango\Cache\CacheInterface The query cache.
     */
    public function getQueryCache()
    {
        return $this->queryCache;
    }

    /**
     * Returns the logger callable.
     *
     * @return mixed The logger callable.
     */
    public function getLoggerCallable()
    {
        return $this->loggerCallable;
    }

    /**
     * Returns the UnitOfWork.
     *
     * @return Mandango\UnitOfWork The UnitOfWork.
     */
    public function getUnitOfWork()
    {
        return $this->unitOfWork;
    }

    /**
     * Set a connection.
     *
     * @param string              $name       The connection name.
     * @param Mandango\Connection $connection The connection.
     */
    public function setConnection($name, Connection $connection)
    {
        if (null !== $this->loggerCallable) {
            $connection->setLoggerCallable($this->loggerCallable);
            $connection->setLogDefault(array('connection' => $name));
        } else {
            $connection->setLoggerCallable(null);
        }

        $this->connections[$name] = $connection;
    }

    /**
     * Set the connections.
     *
     * @param array $connections An array of connections.
     */
    public function setConnections(array $connections)
    {
        $this->connections = array();
        foreach ($connections as $name => $connection) {
            $this->setConnection($name, $connection);
        }
    }

    /**
     * Remove a connection.
     *
     * @param string $name The connection name.
     *
     * @throws \InvalidArgumentException If the connection does not exists.
     */
    public function removeConnection($name)
    {
        if (!$this->hasConnection($name)) {
            throw new \InvalidArgumentException(sprintf('The connection "%s" does not exists.', $name));
        }

        unset($this->connections[$name]);
    }

    /**
     * Clear the connections.
     */
    public function clearConnections()
    {
        $this->connections = array();
    }

    /**
     * Returns if a connection exists.
     *
     * @param string $name The connection name.
     *
     * @return boolean Returns if a connection exists.
     */
    public function hasConnection($name)
    {
        return isset($this->connections[$name]);
    }

    /**
     * Return a connection.
     *
     * @param string $name The connection name.
     *
     * @return Mandango\Connection The connection.
     *
     * @throws \InvalidArgumentException If the connection does not exists.
     */
    public function getConnection($name)
    {
        if (!$this->hasConnection($name)) {
            throw new \InvalidArgumentException(sprintf('The connection "%s" does not exist.', $name));
        }

        return $this->connections[$name];
    }

    /**
     * Returns the connections.
     *
     * @return array The array of connections.
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * Set the default connection name.
     *
     * @param string $name The connection name.
     */
    public function setDefaultConnectionName($name)
    {
        $this->defaultConnectionName = $name;
    }

    /**
     * Returns the default connection name.
     *
     * @return string The default connection name.
     */
    public function getDefaultConnectionName()
    {
        return $this->defaultConnectionName;
    }

    /**
     * Returns the default connection.
     *
     * @return Mandango\Connection The default connection.
     *
     * @throws \RuntimeException If there is not default connection name.
     * @throws \RuntimeException If the default connection does not exists.
     */
    public function getDefaultConnection()
    {
        if (null === $this->defaultConnectionName) {
            throw new \RuntimeException('There is not default connection name.');
        }

        if (!isset($this->connections[$this->defaultConnectionName])) {
            throw new \RuntimeException(sprintf('The default connection "%s" does not exists.', $this->defaultConnectionName));
        }

        return $this->connections[$this->defaultConnectionName];
    }

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
    public function getRepository($documentClass)
    {
        if (!isset($this->repositories[$documentClass])) {
            if (!$this->metadata->hasClass($documentClass) || !$this->metadata->isDocumentClass($documentClass)) {
                throw new \InvalidArgumentException(sprintf('The class "%s" is not a valid document class.', $documentClass));
            }

            $repositoryClass = $documentClass.'Repository';
            if (!class_exists($repositoryClass)) {
                throw new \RuntimeException(sprintf('The class "%s" does not exists.', $repositoryClass));
            }

            $this->repositories[$documentClass] = new $repositoryClass($this);
        }

        return $this->repositories[$documentClass];
    }

    /**
     * Returns all repositories.
     *
     * @return array All repositories.
     */
    public function getAllRepositories()
    {
        foreach ($this->getMetadata()->getDocumentClasses() as $class) {
            $this->getRepository($class);
        }

        return $this->repositories;
    }

    /**
     * Ensure the indexes of all repositories.
     */
    public function ensureAllIndexes()
    {
        foreach ($this->getAllRepositories() as $repository) {
            $repository->ensureIndexes();
        }
    }

    /**
     * Access to repository ->find() method.
     *
     * The first argument is the documentClass of repository.
     *
     * @see Mandango\Repository::find()
     */
    public function find($documentClass, array $query = array(), array $options = array())
    {
        return $this->getRepository($documentClass)->find($query, $options);
    }

    /**
     * Access to repository ->count() method.
     *
     * The first argument is the documentClass of repository.
     *
     * @see Mandango\Repository::count()
     */
    public function count($documentClass, array $query = array())
    {
        return $this->getRepository($documentClass)->count($query);
    }

    /**
     * Access to UnitOfWork ->persist() method.
     *
     * @see Mandango\UnitOfWork::persist()
     */
    public function persist($document)
    {
        $this->unitOfWork->persist($document);
    }

    /**
     * Access to UnitOfWork ->remove() method.
     *
     * @see Mandango\UnitOfWork::remove()
     */
    public function remove($document)
    {
        $this->unitOfWork->remove($document);
    }

    /**
     * Access to UnitOfWork ->commit() method.
     *
     * @see Mandango\UnitOfWork::commit()
     */
    public function flush()
    {
        $this->unitOfWork->commit();
    }
}
