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
 * The identity map class.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class IdentityMap
{
    protected $documents = array();

    /**
     * Set a document.
     *
     * @param \MongoId                    $id       The document id.
     * @param \Mandango\Document\Document $document The document.
     */
    public function set(\MongoId $id, Document $document)
    {
        $this->documents[$id->__toString()] = $document;
    }

    /**
     * Returns if exists a document.
     *
     * @param \MongoId $id The document id.
     *
     * @return boolean If exists or not the document.
     */
    public function has(\MongoId $id)
    {
        return isset($this->documents[$id->__toString()]);
    }

    /**
     * Returns a document.
     *
     * @param \MongoId $id The document id.
     *
     * @return Mandango\Document\Document The document.
     */
    public function get(\MongoId $id)
    {
        return $this->documents[$id->__toString()];
    }

    /**
     * Returns all documents.
     *
     * @return array The documents.
     */
    public function all()
    {
        return $this->documents;
    }

    public function &allByReference()
    {
        return $this->documents;
    }

    /**
     * Remove a document.
     *
     * @param \MongoId $id The document id.
     */
    public function remove(\MongoId $id)
    {
        unset($this->documents[$id->__toString()]);
    }

    /**
     * Clear the documents.
     */
    public function clear()
    {
        $this->documents = array();
    }
}
