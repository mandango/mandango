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
 * RepositoryInterface.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
interface RepositoryInterface
{
    /**
     * Returns the Mandango.
     *
     * @return Mandango The Mandango.
     *
     * @api
     */
    function getMandango();

    /**
     * Returns the identity map.
     *
     * @return IdentityMapInterface The identity map.
     *
     * @api
     */
    function getIdentityMap();

    /**
     * Returns the document class.
     *
     * @return string The document class.
     *
     * @api
     */
    function getDocumentClass();

    /**
     * Returns the metadata.
     *
     * @return array The metadata.
     *
     * @api
     */
    function getMetadata();

    /**
     * Returns if the document is a file (if it uses GridFS).
     *
     * @return boolean If the document is a file.
     *
     * @api
     */
    function isFile();

    /**
     * Returns the connection name, or null if it is the default.
     *
     * @return string|null The connection name.
     *
     * @api
     */
    function getConnectionName();

    /**
     * Returns the collection name.
     *
     * @return string The collection name.
     *
     * @api
     */
    function getCollectionName();

    /**
     * Returns the connection.
     *
     * @return ConnectionInterface The connection.
     *
     * @api
     */
    function getConnection();

    /**
     * Returns the collection.
     *
     * @return \MongoCollection The collection.
     *
     * @api
     */
    function getCollection();

    /**
     * Create a query for the repository document class.
     *
     * @param array $criteria The criteria for the query (optional).
     *
     * @return Query The query.
     *
     * @api
     */
    function createQuery(array $criteria = array());

    /**
     * Find documents by id.
     *
     * @param array $ids An array of ids.
     *
     * @return array An array of documents.
     *
     * @api
     */
    function findById(array $ids);

    /**
     * Returns one document by id.
     *
     * @param mixed $id An id.
     *
     * @return Document|null The document or null if it does not exist.
     *
     * @api
     */
    function findOneById($id);

    /**
     * Count documents.
     *
     * @param array $query The query (opcional, by default an empty array).
     *
     * @return integer The number of documents.
     *
     * @api
     */
    function count(array $query = array());

    /**
     * Remove documents.
     *
     * @param array $query The query (optional, by default an empty array).
     *
     * @return mixed The result of the remove collection method.
     *
     * @api
     */
    function remove(array $query = array());

    /**
     * Shortcut to the collection group method.
     *
     * @param mixed $keys    The keys.
     * @param array $initial The initial value.
     * @param mixes $reduce  The reduce function.
     * @param array $options The options (optional).
     *
     * @return array The result
     *
     * @see \MongoCollection::group()
     *
     * @api
     */
    function group($keys, array $initial, $reduce, array $options = array());

    /**
     * Shortcut to make a distinct command.
     *
     * @param string $field The field.
     * @param array  $query The query (optional).
     *
     * @return array The results.
     *
     * @api
     */
    function distinct($field, array $query = array());
}
