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

use Mandango\Document\Document;

/**
 * IdentityMapInterface.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
interface IdentityMapInterface
{
    /**
     * Set a document.
     *
     * @param mixed    $id       The document Id.
     * @param Document $document The document.
     *
     * @api
     */
    function set($id, Document $document);

    /**
     * Returns if exists a document.
     *
     * @param mixed $id The document id.
     *
     * @return boolean If exists or not the document.
     *
     * @api
     */
    function has($id);

    /**
     * Returns a document.
     *
     * @param mixed $id The document Id.
     *
     * @return Document The document.
     *
     * @api
     */
    function get($id);

    /**
     * Returns all documents.
     *
     * @return array The documents.
     *
     * @api
     */
    function all();

    /**
     * Returns all the documents by reference.
     *
     * @return array The documents by reference.
     *
     * @api
     */
    function &allByReference();

    /**
     * Remove a document.
     *
     * @param mixed $id The document Id.
     *
     * @api
     */
    function remove($id);

    /**
     * Clear the documents.
     *
     * @api
     */
    function clear();
}
