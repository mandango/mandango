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

namespace Mandango\Document;

use Mandango\Archive;

/**
 * The base class for documents.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
abstract class Document extends AbstractDocument
{
    protected $id;

    /**
     * Returns the repository.
     *
     * @return Mandango\Repository The repository of the document.
     */
    static public function repository()
    {
        return static::mandango()->getRepository(get_called_class());
    }

    /**
     * Returns the collection.
     *
     * @return \MongoCollection The collection.
     */
    static public function collection()
    {
        return static::repository()->collection();
    }

    /**
     * Access to repository ->query() method.
     *
     * @see Mandango\Repository::query()
     */
    static public function query(array $criteria = array())
    {
        return static::repository()->query($criteria);
    }

    /**
     * Access to repository ->find() method.
     *
     * @see Mandango\Repository::find()
     */
    static public function find($id)
    {
        return static::repository()->find($id);
    }

    /**
     * Access to repository ->count() method.
     *
     * @see Mandango\Repository::count()
     */
    static public function count(array $criteria = array())
    {
        return static::repository()->count($criteria);
    }

    /**
     * INTERNAL. Set the id of the document.
     *
     * @param \MongoId $id The id.
     *
     * @return Mandango\Document\Document The document (fluent interface).
     */
    public function setId(\MongoId $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Returns the id of document.
     *
     * @return \MongoId|null The id of the document or null if it is new.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * INTERNAL. Returns and remove the id.
     *
     * @return \MongoId|null The id of the document or null if it is new.
     */
    public function getAndRemoveId()
    {
        $id = $this->id;
        $this->id = null;

        return $id;
    }

    /**
     * Returns if the document is new.
     *
     * @return bool Returns if the document is new.
     */
    public function isNew()
    {
        return null === $this->id;
    }

    /**
     * Refresh the document data from the database.
     *
     * @return Mandango\Document\Document The document (fluent interface).
     */
    public function refresh()
    {
        if ($this->isNew()) {
            throw new \LogicException('The document is new.');
        }

        $this->setDocumentData(static::collection()->findOne(array('_id' => $this->getId())), true);

        return $this;
    }

    /**
     * Save the document.
     *
     * @return Mandango\Document\Document The document (fluent interface).
     */
    public function save()
    {
        static::repository()->save($this);

        return $this;
    }

    /**
     * Delete the document.
     */
    public function delete()
    {
        static::repository()->delete($this);
    }

    /**
     * INTERNAL. Adds a query hash.
     *
     * @param string $hash The query hash.
     */
    public function addQueryHash($hash)
    {
        $queryHashes =& Archive::getByRef($this, 'query_hashes', array());
        $queryHashes[] = $hash;
    }

    /**
     * INTERNAL. Returns the query hashes.
     *
     * @return array The query hashes.
     */
    public function getQueryHashes()
    {
        return Archive::getOrDefault($this, 'query_hashes', array());
    }

    /**
     * INTERNAL. Removes a query hash.
     *
     * @param string $hash The query hash.
     */
    public function removeQueryHash($hash)
    {
        $queryHashes =& Archive::getByRef($this, 'query_hashes', array());
        unset($queryHashes[array_search($hash, $queryHashes)]);
        $queryHashes = array_values($queryHashes);
    }

    /**
     * INTERNAL. Clear the query hashes.
     */
    public function clearQueryHashes()
    {
        Archive::remove($this, 'query_hashes');
    }

    /**
     * INTERNAL. Add a field cache.
     */
    public function addFieldCache($field)
    {
        $queryCache = static::mandango()->getQueryCache();

        foreach ($this->getQueryHashes() as $hash) {
            $cache = $queryCache->has($hash) ? $queryCache->get($hash) : array();
            $cache['fields'][$field] = 1;
            $queryCache->set($hash, $cache);
        }
    }
}
