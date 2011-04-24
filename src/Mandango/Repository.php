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
 * The base class for repositories.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
abstract class Repository
{
    /*
     * abstract string The document class.
     *
     * protected $documentClass;
     */

    /*
     * abstract boolean If the document is a file (if it uses GridFS).
     *
     * protected $isFile;
     */

    /*
     * abstract string|null The connection name.
     *
     * protected $connectionName;
     */

    /*
     * abstract string The collection name.
     *
     * protected $collectionName;
     */

    private $mandango;
    private $identityMap;
    private $connection;
    private $collection;

    /**
     * Constructor.
     *
     * @param Mandango\Mandango $mandango The mandango.
     */
    public function __construct(Mandango $mandango)
    {
        $this->mandango = $mandango;
        $this->identityMap = new IdentityMap();
    }

    /**
     * Returns the Mandango.
     *
     * @return Mandango\Mandango The Mandango.
     */
    public function getMandango()
    {
        return $this->mandango;
    }

    /**
     * Returns the identity map.
     *
     * @return Mandango\IdentityMap The identity map.
     */
    public function getIdentityMap()
    {
        return $this->identityMap;
    }

    /**
     * Returns the document class.
     *
     * @return string The document class.
     */
    public function getDocumentClass()
    {
        return $this->documentClass;
    }

    /**
     * Returns if the document is a file (if it uses GridFS).
     *
     * @return boolean If the document is a file.
     */
    public function isFile()
    {
        return $this->isFile;
    }

    /**
     * Returns the connection name, or null if it is the default.
     *
     * @return string|null The connection name.
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * Returns the collection name.
     *
     * @return string The collection name.
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }

    /**
     * Returns the connection.
     *
     * @return Mandango\Connection The connection.
     */
    public function getConnection()
    {
        if (!$this->connection) {
            if ($this->connectionName) {
                $this->connection = $this->mandango->getConnection($this->connectionName);
            } else {
                $this->connection = $this->mandango->getDefaultConnection();
            }
        }

        return $this->connection;
    }

    /**
     * Returns the collection.
     *
     * @return \MongoCollection The collection.
     */
    public function getCollection()
    {
        if (!$this->collection) {
            // gridfs
            if ($this->isFile) {
                $this->collection = $this->getConnection()->getMongoDB()->getGridFS($this->collectionName);
            // normal
            } else {
                $this->collection = $this->getConnection()->getMongoDB()->selectCollection($this->collectionName);
            }
        }

        return $this->collection;
    }

    /**
     * Create a query for the repository document class.
     *
     * @param array $criteria The criteria for the query (optional).
     *
     * @return Mandango\Query The query.
     */
    public function createQuery(array $criteria = array())
    {
        $class = $this->documentClass.'Query';
        $query = new $class($this);
        $query->criteria($criteria);

        return $query;
    }

    /**
     * Find documents by id.
     *
     * @param array $ids An array of ids.
     *
     * @return array An array of documents.
     */
    public function findById(array $ids)
    {
        foreach ($ids as &$id) {
            if (!is_string($id)) {
                $id = new \MongoId($id);
            }
        }
        unset($id);

        $documents = array();
        foreach ($ids as $id) {
            if ($this->identityMap->has($id)) {
                $documents[(string) $id] = $this->identityMap->get($id);
            }
        }

        if (count($documents) == count($ids)) {
            return $documents;
        }

        return $this->createQuery(array('_id' => array('$in' => $ids)))->all();
    }

    /**
     * Returns one document by id.
     *
     * @param mixed $id An id.
     *
     * @return Mandango\Docment\Document|null The document or null if it does not exist.
     */
    public function findOneById($id)
    {
        if (is_string($id)) {
            $id = new \MongoId($id);
        }

        if ($this->identityMap->has($id)) {
            return $this->identityMap->get($id);
        }

        return $this->createQuery(array('_id' => $id))->one();
    }

    /**
     * Count documents.
     *
     * @param array $query The query (opcional, by default an empty array).
     *
     * @return integer The number of documents.
     */
    public function count(array $query = array())
    {
        return $this->getCollection()->count($query);
    }

    /**
     * Remove documents.
     *
     * @param array $query The query (optional, by default an empty array).
     *
     * @return mixed The result of the remove collection method.
     */
    public function remove(array $query = array())
    {
        return $this->getCollection()->remove($query, array('safe' => true));
    }
}
