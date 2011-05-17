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
 * The base class for repositories.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
abstract class Repository implements RepositoryInterface
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
     * @param Mandango $mandango The mandango.
     *
     * @api
     */
    public function __construct(Mandango $mandango)
    {
        $this->mandango = $mandango;
        $this->identityMap = new IdentityMap();
    }

    /**
     * {@inheritdoc}
     */
    public function getMandango()
    {
        return $this->mandango;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityMap()
    {
        return $this->identityMap;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentClass()
    {
        return $this->documentClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return $this->mandango->getMetadataFactory()->getClass($this->documentClass);
    }

    /**
     * {@inheritdoc}
     */
    public function isFile()
    {
        return $this->isFile;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function createQuery(array $criteria = array())
    {
        $class = $this->documentClass.'Query';
        $query = new $class($this);
        $query->criteria($criteria);

        return $query;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function count(array $query = array())
    {
        return $this->getCollection()->count($query);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(array $query = array())
    {
        return $this->getCollection()->remove($query, array('safe' => true));
    }

    /**
     * {@inheritdoc}
     */
    public function group($keys, array $initial, $reduce, array $options = array())
    {
        return $this->getCollection()->group($keys, $initial, $reduce, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function distinct($field, array $query = array())
    {
        return $this->getConnection()->getMongoDB()->command(array(
            'distinct' => $this->getCollectionName(),
            'key'      => $field,
            'query'    => $query,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function mapReduce($map, $reduce, array $out, array $query = array(), array $options = array())
    {
        $command = array_merge($options, array(
            'mapreduce' => $this->getCollectionName(),
            'map'       => is_string($map) ? new \MongoCode($map) : $map,
            'reduce'    => is_string($reduce) ? new \MongoCode($reduce) : $reduce,
            'out'       => $out,
            'query'     => $query,
        ));

        $result = $this->getConnection()->getMongoDB()->command($command);

        if (!$result['ok']) {
            throw new \RuntimeException($result['errmsg']);
        }

        if (isset($out['inline']) && $out['inline']) {
            return $result['results'];
        }

        return $this->getConnection()->getMongoDB()->selectCollection($result['result'])->find();
    }
}
