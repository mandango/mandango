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
 * UnitOfWork.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
class UnitOfWork implements UnitOfWorkInterface
{
    private $mandango;
    private $persist;
    private $remove;

    /**
     * Constructor.
     *
     * @param Mandango\Mandango $mandango The mandango.
     *
     * @api
     */
    public function __construct(Mandango $mandango)
    {
        $this->mandango = $mandango;
        $this->persist = array();
        $this->remove = array();
    }

    /**
     * Returns the mandango.
     *
     * @return Mandango\Mandango The mandango.
     *
     * @api
     */
    public function getMandango()
    {
        return $this->mandango;
    }

    /**
     * {@inheritdoc}
     */
    public function persist($documents)
    {
        if (!is_array($documents)) {
            $documents = array($documents);
        }

        foreach ($documents as $document) {
            $class = get_class($document);
            $oid = spl_object_hash($document);

            if (isset($this->remove[$class][$oid])) {
                unset($this->remove[$class][$oid]);
            }

            $this->persist[$class][$oid] = $document;
        }
    }

    /**
     * Returns if a document is pending for persist.
     *
     * @param Mandango\Document\Document A document.
     *
     * @return bool If the document is pending for persist.
     *
     * @api
     */
    public function isPendingForPersist(Document $document)
    {
        return isset($this->persist[get_class($document)][spl_object_hash($document)]);
    }

    /**
     * Returns if there are pending persist operations.
     *
     * @return boolean If there are pending persist operations.
     *
     * @api
     */
    public function hasPendingForPersist()
    {
        return (bool) count($this->persist);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($documents)
    {
        if (!is_array($documents)) {
            $documents = array($documents);
        }

        foreach ($documents as $document) {
            $class = get_class($document);
            $oid = spl_object_hash($document);

            if (isset($this->persist[$class][$oid])) {
                unset($this->persist[$class][$oid]);
            }

            $this->remove[$class][$oid] = $document;
        }
    }

    /**
     * Returns if a document is pending for remove.
     *
     * @param \Mandango\Document\Document A document.
     *
     * @return bool If the document is pending for remove.
     *
     * @api
     */
    public function isPendingForRemove(Document $document)
    {
        return isset($this->remove[get_class($document)][spl_object_hash($document)]);
    }

    /**
     * Returns if there are pending remove operations.
     *
     * @return boolean If there are pending remove operations.
     *
     * @api
     */
    public function hasPendingForRemove()
    {
        return (bool) count($this->remove);
    }

    /**
     * Returns if there are pending operations.
     *
     * @return boolean If there are pending operations.
     *
     * @api
     */
    public function hasPending()
    {
        return $this->hasPendingForPersist() || $this->hasPendingForRemove();
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        // execute
        foreach ($this->persist as $class => $documents) {
            $this->mandango->getRepository($class)->save($documents);
        }
        foreach ($this->remove as $class => $documents) {
            $this->mandango->getRepository($class)->delete($documents);
        }

        // clear
        $this->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->persist = array();
        $this->remove = array();
    }
}
