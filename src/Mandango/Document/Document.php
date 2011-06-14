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
    protected $id;

    /**
     * Returns the repository.
     *
     * @return Mandango\Repository The repository.
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
     * @param MongoId $id The id.
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
     *
     * @api
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns and remove the id.
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
     *
     * @api
     */
    public function isNew()
    {
        return null === $this->id;
    }

    /**
     * Refresh the document data from the database.
     *
     * @return Mandango\Document\Document The document (fluent interface).
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
     * @var array $options 
     * @return Mandango\Document\Document The document (fluent interface).
     *
     * @api
     */
    public function save(array $options = array())
    {
        $this->getRepository()->save($this, $options);

        return $this;
    }

    /**
     * Ssafe save the document.
     *
     * @return Mandango\Document\Document The document (fluent interface).
     *
     * @api
     */
    public function safeSave(array $options = array())
    {
        $options['safe'] = true;
        return $this->save($options);
    }

    /**
     * Delete the document.
     *
     * @api
     */
    public function delete()
    {
        $this->getRepository()->delete($this);
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
