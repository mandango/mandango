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
 * EmbeddedGroup.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class EmbeddedGroup extends Group
{
    /**
     * Set the root and path of the embedded group.
     *
     * @param Mandango\Document\Docuemnt $root The root document.
     * @param string                     $path The path.
     */
    public function setRootAndPath(Document $root, $path)
    {
        Archive::set($this, 'root_and_path', array('root' => $root, 'path' => $path));

        foreach ($this->getAdd() as $key => $document) {
            $document->setRootAndPath($root, $path.'._add'.$key);
        }
    }

    /**
     * Returns the root and the path.
     */
    public function getRootAndPath()
    {
        return Archive::getOrDefault($this, 'root_and_path', null);
    }

    /**
     * {@inheritdoc}
     */
    public function add($documents)
    {
        parent::add($documents);

        if ($rap = $this->getRootAndPath()) {
            foreach ($this->getAdd() as $key => $document) {
                $document->setRootAndPath($rap['root'], $rap['path'].'._add'.$key);
            }
        }
    }

    /**
     * Set the saved data.
     *
     * @param array $data The saved data.
     */
    public function setSavedData(array $data)
    {
        Archive::set($this, 'saved_data', $data);
    }

    /**
     * Returns the saved data.
     *
     * @return array|null The saved data or null if it does not exist.
     */
    public function getSavedData()
    {
        return Archive::getOrDefault($this, 'saved_data', null);
    }

    /**
     * {@inheritdoc}
     */
    protected function doInitializeSavedData()
    {
        if ($data = $this->getSavedData()) {
            return $data;
        }

        $rap = $this->getRootAndPath();

        if ($rap['root']->isNew()) {
            return array();
        }

        $rap['root']->addFieldCache($rap['path']);

        $result = call_user_func(array(get_class($rap['root']), 'collection'))
            ->findOne(array('_id' => $rap['root']->getId()), array($rap['path']))
        ;

        return ($result && isset($result[$rap['path']])) ? $result[$rap['path']] : array();
    }

    /**
     * {@inheritdoc}
     */
    protected function doInitializeSaved(array $data)
    {
        $documentClass = $this->getDocumentClass();
        $rap = $this->getRootAndPath();

        $saved = array();
        foreach ($data as $key => $datum) {
            $saved[] = $document = new $documentClass();
            $document->setDocumentData($datum);
            $document->setRootAndPath($rap['root'], $rap['path'].'.'.$key);
        }

        return $saved;
    }
}
