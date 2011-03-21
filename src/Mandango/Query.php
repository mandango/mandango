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
 */
class Query implements \Countable, \IteratorAggregate
{
    protected $repository;
    protected $hash;

    protected $criteria = array();
    protected $fields = array('_id' => 1);
    protected $references = array();
    protected $sort;
    protected $limit;
    protected $skip;
    protected $batchSize;
    protected $hint;
    protected $snapshot = false;
    protected $timeout;

    /**
     * Constructor.
     *
     * @param string Mandango\Repository The repository of the document class to query.
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

        if ($fields = $this->getFieldsCache()) {
            $this->fields = $fields;
        }
    }

    /**
     * Returns the repository.
     *
     * @return Mandango\Repository The repository.
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
     * @throws \InvalidArgumentException If the criteria is not an array or null.
     */
    public function criteria($criteria)
    {
        if (null !== $criteria && !is_array($criteria)) {
            throw new \InvalidArgumentException(sprintf('The criteria "%s" is not valid.', $criteria));
        }

        $this->criteria = $criteria;

        return $this;
    }

    /**
     * Returns the criteria.
     *
     * @return array The criteria.
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
     * @throws \InvalidArgumentException If the fields are not an array or null.
     */
    public function fields($fields)
    {
        if (null !== $fields && !is_array($fields)) {
            throw new \InvalidArgumentException(sprintf('The fields "%s" are not valid.', $fields));
        }

        $this->fields = $fields;

        return $this;
    }

    /**
     * Returns the fields.
     *
     * @return array The fields.
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
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Returns all the results.
     *
     * @return array An array with all the results.
     */
    public function all()
    {
        $documentClass = $this->repository->getDocumentClass();
        $identityMap =& $this->repository->getIdentityMap()->allByReference();
        $isFile = $this->repository->isFile();

        $fields = array();
        foreach (array_keys($this->fields) as $field) {
            if (false === strpos($field, '.')) {
                $fields[$field] = 1;
                continue;
            }
            $f =& $fields;
            foreach (explode('.', $field) as $name) {
                if (!isset($f[$name])) {
                    $f[$name] = array();
                }
                $f =& $f[$name];
            }
            $f = 1;
        }

        $documents = array();
        foreach ($this->createCursor() as $id => $data) {
            if (isset($identityMap[$id])) {
                $document = $identityMap[$id];
                $document->addQueryHash($this->hash);
            } else {
                if ($isFile) {
                    $file = $data;
                    $data = $file->file;
                    $data['file'] = $file;
                }
                $data['_query_hash'] = $this->hash;
                $data['_fields'] = $fields;

                $document = new $documentClass();
                $document->setDocumentData($data);

                $identityMap[$id] = $document;
            }
            $documents[$id] = $document;
        }

        if ($this->references) {
            $mandango = $this->repository->getMandango();
            $metadata = $mandango->getMetadata()->getClassInfo($this->repository->getDocumentClass());
            foreach ($this->references as $referenceName) {
                // one
                if (isset($metadata['references_one'][$referenceName])) {
                    $reference = $metadata['references_one'][$referenceName];
                    $field = $reference['field'];

                    $ids = array();
                    foreach ($documents as $document) {
                        if ($id = $document->get($field)) {
                            $ids[] = $id;
                        }
                    }
                    if ($ids) {
                        $mandango->getRepository($reference['class'])->find(array_unique($ids));
                    }

                    continue;
                }

                // many
                if (isset($metadata['references_many'][$referenceName])) {
                    $reference = $metadata['references_many'][$referenceName];
                    $field = $reference['field'];

                    $ids = array();
                    foreach ($documents as $document) {
                        if ($id = $document->get($field)) {
                            foreach ($id as $i) {
                                $ids[] = $i;
                            }
                        }
                    }
                    if ($ids) {
                        $mandango->getRepository($reference['class'])->find(array_unique($ids));
                    }

                    continue;
                }

                // invalid
                throw new \RuntimeException(sprintf('The reference "%s" does not exist in the class "%s".', $referenceName, $documentClass));
            }
        }

        return $documents;
    }

    /**
     * Returns an \ArrayIterator with all results (implements \IteratorAggregate interface).
     *
     * @return \ArrayIterator An \ArrayIterator with all results.
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * Returns one result.
     *
     * @return Mandango\Document\Document|null A document or null if there is no any result.
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
        $cursor = $this->repository->collection()->find($this->criteria, $this->fields);

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
