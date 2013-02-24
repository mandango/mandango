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

use Mandango\Repository;

/**
 * Query.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
abstract class Query implements \Countable, \IteratorAggregate
{
    private $repository;
    private $hash;

    private $criteria;
    private $fields;
    private $references;
    private $sort;
    private $limit;
    private $skip;
    private $batchSize;
    private $hint;
    private $slaveOkay;
    private $snapshot;
    private $timeout;

    /**
     * Constructor.
     *
     * @param \Mandango\Repository $repository The repository of the document class to query.
     *
     * @api
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;

        $hash = $this->repository->getDocumentClass();

        if (version_compare(PHP_VERSION, '5.3.6', '=>')) {
            $debugBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        } else {
            $debugBacktrace = debug_backtrace();
        }
        foreach ($debugBacktrace as $value) {
            if (isset($value['function'])) {
                $hash .= $value['function'];
            }
            if (isset($value['class'])) {
                $hash .= $value['class'];
            }
            if (isset($value['type'])) {
                $hash .= $value['type'];
            }
            if (isset($value['file'])) {
                $hash .= $value['file'];
            }
            if (isset($value['line'])) {
                $hash .= $value['line'];
            }
        }
        $this->hash = md5($hash);

        $this->criteria = array();
        $this->fields = array('_id' => 1);
        $this->references = array();
        $this->snapshot = false;

        if ($fields = $this->getFieldsCache()) {
            $this->fields = $fields;
        }
        if ($references = $this->getReferencesCache()) {
            $this->references = $references;
        }
    }

    /**
     * Returns the repository.
     *
     * @return \Mandango\Repository The repository.
     *
     * @api
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Returns the query hash.
     *
     * @return string The query hash.
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Returns the fields in cache.
     *
     * @return array|null The fields in cache, or null if there is not.
     */
    public function getFieldsCache()
    {
        $cache = $this->repository->getMandango()->getCache()->get($this->hash);

        return ($cache && isset($cache['fields'])) ? $cache['fields'] : null;
    }

    /**
     * Returns the references in cache.
     *
     * @return array|null The references in cache, or null if there is not.
     */
    public function getReferencesCache()
    {
        $cache = $this->repository->getMandango()->getCache()->get($this->hash);

        return ($cache && isset($cache['references'])) ? $cache['references'] : null;
    }

    /**
     * Set the criteria.
     *
     * @param array $criteria The criteria.
     *
     * @return \Mandango\Query The query instance (fluent interface).
     *
     * @api
     */
    public function criteria(array $criteria)
    {
        $this->criteria = $criteria;

        return $this;
    }

    /**
     * Merges a criteria with the current one.
     *
     * @param array $criteria The criteria.
     *
     * @return \Mandango\Query The query instance (fluent interface).
     *
     * @api
     */
    public function mergeCriteria(array $criteria)
    {
        $this->criteria = null === $this->criteria ? $criteria : array_merge($this->criteria, $criteria);

        return $this;
    }

    /**
     * Returns the criteria.
     *
     * @return array The criteria.
     *
     * @api
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Set the fields.
     *
     * @param array $fields The fields.
     *
     * @return \Mandango\Query The query instance (fluent interface).
     *
     * @api
     */
    public function fields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Returns the fields.
     *
     * @return array The fields.
     *
     * @api
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Set the references.
     *
     * @param array $references The references.
     *
     * @return \Mandango\Query The query instance (fluent interface).
     *
     * @throws \InvalidArgumentException If the references are not an array or null.
     *
     * @api
     */
    public function references($references)
    {
        if (null !== $references && !is_array($references)) {
            throw new \InvalidArgumentException(sprintf('The references "%s" are not valid.', $references));
        }

        $this->references = $references;

        return $this;
    }

    /**
     * Returns the references.
     *
     * @return array The references.
     *
     * @api
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * Set the sort.
     *
     * @param array|null $sort The sort.
     *
     * @return \Mandango\Query The query instance (fluent interface).
     *
     * @throws \InvalidArgumentException If the sort is not an array or null.
     *
     * @api
     */
    public function sort($sort)
    {
        if (null !== $sort && !is_array($sort)) {
            throw new \InvalidArgumentException(sprintf('The sort "%s" is not valid.', $sort));
        }

        $this->sort = $sort;

        return $this;
    }

    /**
     * Returns the sort.
     *
     * @return array The sort.
     *
     * @api
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Set the limit.
     *
     * @param int|null $limit The limit.
     *
     * @return \Mandango\Query The query instance (fluent interface).
     *
     * @throws \InvalidArgumentException If the limit is not a valid integer or null.
     *
     * @api
     */
    public function limit($limit)
    {
        if (null !== $limit) {
            if (!is_numeric($limit) || $limit != (int) $limit) {
                throw new \InvalidArgumentException('The limit is not valid.');
            }
            $limit = (int) $limit;
        }

        $this->limit = $limit;

        return $this;
    }

    /**
     * Returns the limit.
     *
     * @return int|null The limit.
     *
     * @api
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set the skip.
     *
     * @param int|null $skip The skip.
     *
     * @return \Mandango\Query The query instance (fluent interface).
     *
     * @throws \InvalidArgumentException If the skip is not a valid integer, or null.
     *
     * @api
     */
    public function skip($skip)
    {
        if (null !== $skip) {
            if (!is_numeric($skip) || $skip != (int) $skip) {
                throw new \InvalidArgumentException('The skip is not valid.');
            }
            $skip = (int) $skip;
        }

        $this->skip = $skip;

        return $this;
    }

    /**
     * Returns the skip.
     *
     * @return int|null The skip.
     *
     * @api
     */
    public function getSkip()
    {
        return $this->skip;
    }

    /**
     * Set the batch size.
     *
     * @param int|null $batchSize The batch size.
     *
     * @return \Mandango\Query The query instance (fluent interface).
     *
     * @api
     */
    public function batchSize($batchSize)
    {
        if (null !== $batchSize) {
            if (!is_numeric($batchSize) || $batchSize != (int) $batchSize) {
                throw new \InvalidArgumentException('The batchSize is not valid.');
            }
            $batchSize = (int) $batchSize;
        }

        $this->batchSize = $batchSize;

        return $this;
    }

    /**
     * Returns the batch size.
     *
     * @return int|null The batch size.
     *
     * @api
     */
    public function getBatchSize()
    {
        return $this->batchSize;
    }

    /**
     * Set the hint.
     *
     * @param array|null The hint.
     *
     * @return \Mandango\Query The query instance (fluent interface).
     *
     * @api
     */
    public function hint($hint)
    {
        if (null !== $hint && !is_array($hint)) {
            throw new \InvalidArgumentException(sprintf('The hint "%s" is not valid.', $hint));
        }

        $this->hint = $hint;

        return $this;
    }

    /**
     * Returns the hint.
     *
     * @return array|null The hint.
     *
     * @api
     */
    public function getHint()
    {
        return $this->hint;
    }

    /**
     * Sets the slave okay.
     *
     * @param Boolean|null $okay If it is okay to query the slave (true by default).
     *
     * @return \Mandango\Query The query instance (fluent interface).
     */
    public function slaveOkay($okay = true)
    {
        if (null !== $okay) {
            if (!is_bool($okay)) {
                throw new \InvalidArgumentException('The slave okay is not a boolean.');
            }
        }

        $this->slaveOkay = $okay;

        return $this;
    }

    /**
     * Returns the slave okay.
     *
     * @return Boolean|null The slave okay.
     */
    public function getSlaveOkay()
    {
        return $this->slaveOkay;
    }

    /**
     * Set if the snapshot mode is used.
     *
     * @param bool $snapshot If the snapshot mode is used.
     *
     * @return \Mandango\Query The query instance (fluent interface).
     *
     * @api
     */
    public function snapshot($snapshot)
    {
        if (!is_bool($snapshot)) {
            throw new \InvalidArgumentException('The snapshot is not a boolean.');
        }

        $this->snapshot = $snapshot;

        return $this;
    }

    /**
     * Returns if the snapshot mode is used.
     *
     * @return bool If the snapshot mode is used.
     *
     * @api
     */
    public function getSnapshot()
    {
        return $this->snapshot;
    }

    /**
     * Set the timeout.
     *
     * @param int|null $timeout The timeout of the cursor.
     *
     * @return \Mandango\Query The query instance (fluent interface).
     *
     * @api
     */
    public function timeout($timeout)
    {
        if (null !== $timeout) {
            if (!is_numeric($timeout) || $timeout != (int) $timeout) {
                throw new \InvalidArgumentException('The timeout is not valid.');
            }
            $timeout = (int) $timeout;
        }

        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Returns the timeout.
     *
     * @return int|null The timeout.
     *
     * @api
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Returns all the results.
     *
     * @return array An array with all the results.
     *
     * @api
     */
    abstract public function all();

    /**
     * Returns an \ArrayIterator with all results (implements \IteratorAggregate interface).
     *
     * @return \ArrayIterator An \ArrayIterator with all results.
     *
     * @api
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * Returns one result.
     *
     * @return \Mandango\Document\Document|null A document or null if there is no any result.
     *
     * @api
     */
    public function one()
    {
        $currentLimit = $this->limit;
        $results = $this->limit(1)->all();
        $this->limit = $currentLimit;

        return $results ? array_shift($results) : null;
    }

    /**
     * Count the number of results of the query.
     *
     * @return int The number of results of the query.
     *
     * @api
     */
    public function count()
    {
        return $this->createCursor()->count();
    }

    /**
     * Create a cursor with the data of the query.
     *
     * @return \MongoCursor A cursor with the data of the query.
     */
    public function createCursor()
    {
        $cursor = $this->repository->getCollection()->find($this->criteria, $this->fields);

        if (null !== $this->sort) {
            $cursor->sort($this->sort);
        }

        if (null !== $this->limit) {
            $cursor->limit($this->limit);
        }

        if (null !== $this->skip) {
            $cursor->skip($this->skip);
        }

        if (null !== $this->batchSize) {
            $cursor->batchSize($this->batchSize);
        }

        if (null !== $this->hint) {
            $cursor->hint($this->hint);
        }

        if (null !== $this->slaveOkay) {
            $cursor->slaveOkay($this->slaveOkay);
        }

        if ($this->snapshot) {
            $cursor->snapshot();
        }

        if (null !== $this->timeout) {
            $cursor->timeout($this->timeout);
        }

        return $cursor;
    }
}
