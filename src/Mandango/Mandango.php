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

use Mandango\Cache\CacheInterface;

/**
 * Mandango.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
class Mandango
{
    const VERSION = '1.0.0-DEV';

    private $metadataFactory;
    private $queryCache;
    private $loggerCallable;
    private $unitOfWork;
    private $connections;
    private $defaultConnectionName;
    private $repositories;

    /**
     * Constructor.
     *
     * @param Mandango\MetadataFactory      $metadataFactory The metadata factory.
     * @param Mandango\Cache\CacheInterface $queryCache      The query cache.
     * @param mixed                         $loggerCallable  The logger callable (optional, null by default).
     *
     * @api
     */
    public function __construct(MetadataFactory $metadataFactory, CacheInterface $queryCache, $loggerCallable = null)
    {
        $this->metadataFactory = $metadataFactory;
        $this->queryCache = $queryCache;
        $this->loggerCallable = $loggerCallable;
        $this->unitOfWork = new UnitOfWork($this);
        $this->connections = array();
        $this->repositories = array();
    }

    /**
     * Returns the metadata factory.
     *
     * @return MetadataFactory The metadata factory.
     *
     * @api
     */
    public function getMetadataFactory()
    {
        return $this->metadataFactory;
    }

    /**
     * Returns the query cache.
     *
     * @return CacheInterface The query cache.
     *
     * @api
     */
    public function getQueryCache()
    {
        return $this->queryCache;
    }

    /**
     * Returns the logger callable.
     *
     * @return mixed The logger callable.
     *
     * @api
     */
    public function getLoggerCallable()
    {
        return $this->loggerCallable;
    }

    /**
     * Returns the UnitOfWork.
     *
     * @return UnitOfWork The UnitOfWork.
     *
     * @api
     */
    public function getUnitOfWork()
    {
        return $this->unitOfWork;
    }

    /**
     * Set a connection.
     *
     * @param string              $name       The connection name.
     * @param ConnectionInterface $connection The connection.
     *
     * @api
     */
    public function setConnection($name, ConnectionInterface $connection)
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
     *
     * @api
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
     *
     * @api
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
     *
     * @api
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
     *
     * @api
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
     * @return ConnectionInterface The connection.
     *
     * @throws \InvalidArgumentException If the connection does not exists.
     *
     * @api
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
     *
     * @api
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * Set the default connection name.
     *
     * @param string $name The connection name.
     *
     * @api
     */
    public function setDefaultConnectionName($name)
    {
        $this->defaultConnectionName = $name;
    }

    /**
     * Returns the default connection name.
     *
     * @return string The default connection name.
     *
     * @api
     */
    public function getDefaultConnectionName()
    {
        return $this->defaultConnectionName;
    }

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
     * Returns the metadata of a document class.
     *
     * @param string $documentClass The document class.
     *
     * @return array The metadata.
     *
     * @api
     */
    public function getMetadata($documentClass)
    {
        return $this->metadataFactory->getClass($documentClass);
    }

    /**
     * Creates a new document.
     *
     * @param string $documentClass The document class.
     *
     * @return Document The document.
     *
     * @api
     */
    public function create($documentClass)
    {
        $document = new $documentClass($this);
        $document->initializeDefaults();
        $document->initialize();

        return $document;
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
     *
     * @api
     */
    public function getRepository($documentClass)
    {
        if (!isset($this->repositories[$documentClass])) {
            if (!$this->metadataFactory->hasClass($documentClass) || !$this->metadataFactory->isDocumentClass($documentClass)) {
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
     *
     * @api
     */
    public function getAllRepositories()
    {
        foreach ($this->metadataFactory->getDocumentClasses() as $class) {
            $this->getRepository($class);
        }

        return $this->repositories;
    }

    /**
     * Ensure the indexes of all repositories.
     *
     * @api
     */
    public function ensureAllIndexes()
    {
        foreach ($this->getAllRepositories() as $repository) {
            $repository->ensureIndexes();
        }
    }

    /**
     * Access to UnitOfWork ->persist() method.
     *
     * @see UnitOfWork::persist()
     *
     * @api
     */
    public function persist($documents)
    {
        $this->unitOfWork->persist($documents);
    }

    /**
     * Access to UnitOfWork ->remove() method.
     *
     * @see Mandango\UnitOfWork::remove()
     *
     * @api
     */
    public function remove($document)
    {
        $this->unitOfWork->remove($document);
    }

    /**
     * Access to UnitOfWork ->commit() method.
     *
     * @see Mandango\UnitOfWork::commit()
     *
     * @api
     */
    public function flush()
    {
        $this->unitOfWork->commit();
    }
}
