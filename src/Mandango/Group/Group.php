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

namespace Mandango\Group;

use Mandango\Archive;
use Mandango\Document\Document;

/**
 * Group.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
abstract class Group
{
    protected $saved;

    /**
     * Constructor.
     *
     * @param string $documentClass The document class.
     */
    public function __construct($documentClass)
    {
        Archive::set($this, 'document_class', $documentClass);
    }

    /**
     * Returns the document class.
     */
    public function getDocumentClass()
    {
        return Archive::get($this, 'document_class');
    }

    /**
     * Adds document/s to the add queue of the group.
     *
     * @param Mandango\Document\AbstractDocument|array $documents One or more documents.
     */
    public function add($documents)
    {
        if (!is_array($documents)) {
            $documents = array($documents);
        }

        $add =& Archive::getByRef($this, 'add', array());
        foreach ($documents as $document) {
            $add[] = $document;
        }
    }

    /**
     * Returns the add queue of the group.
     */
    public function getAdd()
    {
        return Archive::getOrDefault($this, 'add', array());
    }

    /**
     * Clears the add queue of the group.
     */
    public function clearAdd()
    {
        Archive::remove($this, 'add');
    }

    /**
     * Adds document/s to the remove queue of the group.
     *
     * @param Mandango\Document\AbstractDocument|array $documents One of more documents.
     */
    public function remove($documents)
    {
        if (!is_array($documents)) {
            $documents = array($documents);
        }

        $remove =& Archive::getByRef($this, 'remove', array());
        foreach ($documents as $document) {
            $remove[] = $document;
        }
    }

    /**
     * Returns the remove queue of the group.
     */
    public function getRemove()
    {
        return Archive::getOrDefault($this, 'remove', array());
    }

    /**
     * Clears the remove queue of the group.
     */
    public function clearRemove()
    {
        Archive::remove($this, 'remove');
    }

    /**
     * Returns the saved documents of the group.
     */
    public function saved()
    {
        if (null === $this->saved) {
            $this->initializeSaved();
        }

        return $this->saved;
    }

    /**
     * Refresh the saved documents.
     */
    public function refreshSaved()
    {
        $this->initializeSaved();
    }

    /**
     * Initializes the saved documents.
     */
    protected function initializeSaved()
    {
        $this->saved = $this->doInitializeSaved($this->doInitializeSavedData());
    }

    /**
     * Clears the saved documents.
     */
    public function clearSaved()
    {
        $this->saved = null;
    }

    /**
     * Returns if the saved documents are initialized.
     *
     * @return bool If the saved documents are initialized.
     */
    public function isSavedInitialized()
    {
        return null !== $this->saved;
    }

    /**
     * Do the initialization of the saved documents data.
     */
    abstract protected function doInitializeSavedData();

    /**
     * Do the initialization of the saved documents.
     */
    protected function doInitializeSaved(array $data)
    {
        return $data;
    }

    /**
     * Returns the number of saved documents.
     */
    public function count()
    {
        return count($this->saved());
    }

    /**
     * Replace all documents.
     *
     * @param array $documents An array of documents.
     */
    public function replace(array $documents)
    {
        $this->clearAdd();
        $this->clearRemove();

        $this->remove($this->saved());
        $this->add($documents);
    }
}
