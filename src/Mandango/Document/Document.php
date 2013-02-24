<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Document;

use Mandango\Archive;

/**
 * The base class for documents.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
abstract class Document extends AbstractDocument
{
    private $isNew = true;
    private $id;

    /**
     * Returns the repository.
     *
     * @return \Mandango\Repository The repository.
     *
     * @api
     */
    public function getRepository()
    {
        return $this->getMandango()->getRepository(get_class($this));
    }

    /**
     * Set the id of the document.
     *
     * @param mixed $id The id.
     *
     * @return \Mandango\Document\Document The document (fluent interface).
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Returns the id of document.
     *
     * @return \MongoId|null The id of the document or null if it is new.
     *
     * @api
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * INTERNAL. Returns if the document is new.
     *
     * @param Boolean $isNew If the document is new.
     *
     * @return \Mandango\Document\Document The document (fluent interface).
     */
    public function setIsNew($isNew)
    {
        $this->isNew = (Boolean) $isNew;

        return $this;
    }

    /**
     * Returns if the document is new.
     *
     * @return bool Returns if the document is new.
     *
     * @api
     */
    public function isNew()
    {
        return $this->isNew;
    }

    /**
     * Refresh the document data from the database.
     *
     * @return \Mandango\Document\Document The document (fluent interface).
     *
     * @throws \LogicException
     *
     * @api
     */
    public function refresh()
    {
        if ($this->isNew()) {
            throw new \LogicException('The document is new.');
        }

        $this->setDocumentData($this->getRepository()->getCollection()->findOne(array('_id' => $this->getId())), true);

        return $this;
    }

    /**
     * Save the document.
     *
     * @param array $options The options for the batch insert or update operation, it depends on if the document is new or not (optional).
     *
     * @return \Mandango\Document\Document The document (fluent interface).
     *
     * @api
     */
    public function save(array $options = array())
    {
        if ($this->isNew()) {
            $batchInsertOptions = $options;
            $updateOptions = array();
        } else {
            $batchInsertOptions = array();
            $updateOptions = $options;
        }

        $this->getRepository()->save($this, $batchInsertOptions, $updateOptions);

        return $this;
    }

    /**
     * Delete the document.
     *
     * @param array $options The options for the remove operation (optional).
     *
     * @api
     */
    public function delete(array $options = array())
    {
        $this->getRepository()->delete($this, $options);
    }

    /**
     * Adds a query hash.
     *
     * @param string $hash The query hash.
     */
    public function addQueryHash($hash)
    {
        $queryHashes =& Archive::getByRef($this, 'query_hashes', array());
        $queryHashes[] = $hash;
    }

    /**
     * Returns the query hashes.
     *
     * @return array The query hashes.
     */
    public function getQueryHashes()
    {
        return Archive::getOrDefault($this, 'query_hashes', array());
    }

    /**
     * Removes a query hash.
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
     * Clear the query hashes.
     */
    public function clearQueryHashes()
    {
        Archive::remove($this, 'query_hashes');
    }

    /**
     * Add a field cache.
     */
    public function addFieldCache($field)
    {
        $cache = $this->getMandango()->getCache();

        foreach ($this->getQueryHashes() as $hash) {
            $value = $cache->has($hash) ? $cache->get($hash) : array();
            $value['fields'][$field] = 1;
            $cache->set($hash, $value);
        }
    }

    /**
     * Adds a reference cache
     */
    public function addReferenceCache($reference)
    {
        $cache = $this->getMandango()->getCache();

        foreach ($this->getQueryHashes() as $hash) {
            $value = $cache->has($hash) ? $cache->get($hash) : array();
            if (!isset($value['references']) || !in_array($reference, $value['references'])) {
                $value['references'][] = $reference;
                $cache->set($hash, $value);
            }
        }
    }
}
