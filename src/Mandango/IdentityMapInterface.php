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

use Mandango\Document\Document;

/**
 * IdentityMapInterface.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
interface IdentityMapInterface
{
    /**
     * Set a document.
     *
     * @param mixed    $id       The document Id.
     * @param Document $document The document.
     */
    function set($id, Document $document);

    /**
     * Returns if exists a document.
     *
     * @param mixed $id The document id.
     *
     * @return boolean If exists or not the document.
     */
    function has($id);

    /**
     * Returns a document.
     *
     * @param mixed $id The document Id.
     *
     * @return Document The document.
     */
    function get($id);

    /**
     * Returns all documents.
     *
     * @return array The documents.
     */
    function all();

    /**
     * Returns all the documents by reference.
     *
     * @return array The documents by reference.
     */
    function &allByReference();

    /**
     * Remove a document.
     *
     * @param mixed $id The document Id.
     */
    function remove($id);

    /**
     * Clear the documents.
     */
    function clear();
}
