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
    private $snapshot;
    private $timeout;

    /**
     * Constructor.
     *
     * @param string Mandango\Repository The repository of the document class to query.
     *
     * @api
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;

        $hash = $this->repository->getDocumentClass();
        foreach (debug_backtrace() as $value) {
            if (!isset($value['file'])) {
                $hash .= $value['function'].$value['class'].$value['type'];
            } else {
                $hash .= $value['file'].$value['line'];
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
    }

    /**
     * Returns the repository.
     *
     * @return Mandango\Repository The repository.
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
        $cache = $this->repository->getMandango()->getQueryCache()->get($this->hash);

        return ($cache && isset($cache['fields'])) ? $cache['fields'] : null;
    }

    /**
     * Set the criteria.
     *
     * @param array $criteria The criteria.
     *
     * @return Mandango\Query The query instance (fluent interface).
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
     * @return Mandango\Query The query instance (fluent interface).
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
     * @return Mandango\Query The query instance (fluent interface).
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
     * @return Mandango\Query The query instance (fluent interface).
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
     * @return Mandango\Query The query instance (fluent interface).
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
     * @return Mandango\Query The query instance (fluent interface).
     *
     * @throws \InvalidArgumentException If the limit is not a valid integer or null.
     *
     * @api
     */
    public function limit($limit)
    {
        if (null !== $limit) {
            if (!is_numeric($limit) || $limit != (int) $limit) {
                throw new \InvalidArgumentException(sprintf('The limit "%s" is not valid.', $limit));
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
     * @return Mandango\Query The query instance (fluent interface).
     *
     * @throws \InvalidArgumentException If the skip is not a valid integer, or null.
     *
     * @api
     */
    public function skip($skip)
    {
        if (null !== $skip) {
            if (!is_numeric($skip) || $skip != (int) $skip) {
                throw new \InvalidArgumentException(sprintf('The skip "%s" is not valid.', $skip));
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
     * @return Mandango\Query The query instance (fluent interface).
     *
     * @api
     */
    public function batchSize($batchSize)
    {
        if (null !== $batchSize) {
            if (!is_numeric($batchSize) || $batchSize != (int) $batchSize) {
                throw new \InvalidArgumentException(sprintf('The batchSize "%s" is not valid.', $batchSize));
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
     * @return Mandango\Query The query instance (fluent interface).
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
     * Set if the snapshot mode is used.
     *
     * @param bool $snapshot If the snapshot mode is used.
     *
     * @return Mandango\Query The query instance (fluent interface).
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
     * @return Mandango\Query The query instance (fluent interface).
     *
     * @api
     */
    public function timeout($timeout)
    {
        if (null !== $timeout) {
            if (!is_numeric($timeout) || $timeout != (int) $timeout) {
                throw new \InvalidArgumentException(sprintf('The limit "%s" is not valid.', $timeout));
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
     * @return Mandango\Document\Document|null A document or null if there is no any result.
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

        if ($this->snapshot) {
            $cursor->snapshot();
        }

        if (null !== $this->timeout) {
            $cursor->timeout($this->timeout);
        }

        return $cursor;
    }
}
