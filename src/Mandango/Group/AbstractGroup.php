<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Group;

use Mandango\Archive;
use Mandango\Document\Document;

/**
 * AbstractGroup.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
abstract class AbstractGroup implements \Countable, \IteratorAggregate
{
    private $saved;

    /**
     * Destructor - empties the Archive cache
     */
    public function __destruct()
    {
        Archive::removeObject($this);
    }

    /**
     * Adds document/s to the add queue of the group.
     *
     * @param \Mandango\Document\AbstractDocument|array $documents One or more documents.
     *
     * @api
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
     *
     * @api
     */
    public function getAdd()
    {
        return Archive::getOrDefault($this, 'add', array());
    }

    /**
     * Clears the add queue of the group.
     *
     * @api
     */
    public function clearAdd()
    {
        Archive::remove($this, 'add');
    }

    /**
     * Adds document/s to the remove queue of the group.
     *
     * @param \Mandango\Document\AbstractDocument|array $documents One of more documents.
     *
     * @api
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
     *
     * @api
     */
    public function getRemove()
    {
        return Archive::getOrDefault($this, 'remove', array());
    }

    /**
     * Clears the remove queue of the group.
     *
     * @api
     */
    public function clearRemove()
    {
        Archive::remove($this, 'remove');
    }

    /**
     * Returns the saved documents of the group.
     */
    public function getSaved()
    {
        if (null === $this->saved) {
            $this->initializeSaved();
        }

        return $this->saved;
    }

    /**
     * Returns the saved + add - removed elements.
     *
     * @api
     */
    public function all()
    {
        $documents = array_merge($this->getSaved(), $this->getAdd());

        foreach ($this->getRemove() as $document) {
            if (false !== $key = array_search($document, $documents)) {
                unset($documents[$key]);
            }
        }

        return array_values($documents);
    }

    /**
     * Implements the \IteratorAggregate interface.
     *
     * @api
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * Refresh the saved documents.
     *
     * @api
     */
    public function refreshSaved()
    {
        $this->initializeSaved();
    }

    /**
     * Initializes the saved documents.
     */
    private function initializeSaved()
    {
        $this->saved = $this->doInitializeSaved($this->doInitializeSavedData());
    }

    /**
     * Clears the saved documents.
     *
     * @api
     */
    public function clearSaved()
    {
        $this->saved = null;
    }

    /**
     * Returns if the saved documents are initialized.
     *
     * @return bool If the saved documents are initialized.
     *
     * @api
     */
    public function isSavedInitialized()
    {
        return null !== $this->saved;
    }

    /**
     * Do the initialization of the saved documents data.
     *
     * @api
     */
    abstract protected function doInitializeSavedData();

    /**
     * Do the initialization of the saved documents.
     *
     * @api
     */
    protected function doInitializeSaved(array $data)
    {
        return $data;
    }

    /**
     * Returns the number of all documents.
     *
     * @api
     */
    public function count()
    {
        return count($this->all());
    }

    /**
     * Replace all documents.
     *
     * @param array $documents An array of documents.
     *
     * @api
     */
    public function replace(array $documents)
    {
        $this->clearAdd();
        $this->clearRemove();

        $this->remove($this->getSaved());
        $this->add($documents);
    }

    /**
     * Resets the group (clear adds and removed, and saved if there are adds or removed).
     *
     * @api
     */
    public function reset()
    {
        if ($this->getAdd() || $this->getRemove()) {
            $this->clearSaved();
        }
        $this->clearAdd();
        $this->clearRemove();
    }
}
