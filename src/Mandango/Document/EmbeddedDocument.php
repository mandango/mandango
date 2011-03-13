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

namespace Mandango\Document;

use Mandango\Archive;
use Mandango\Group\EmbeddedGroup;

/**
 * The base class for embedded documents.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
abstract class EmbeddedDocument extends AbstractDocument
{
    /**
     * Set the root and path of the embedded document.
     *
     * @param Mandango\Document\Document $root The root document.
     * @param string                     $path The path.
     */
    public function setRootAndPath(Document $root, $path)
    {
        Archive::set($this, 'root_and_path', array('root' => $root, 'path' => $path));

        if (isset($this->data['embeddeds_one'])) {
            foreach ($this->data['embeddeds_one'] as $name => $embedded) {
                $embedded->setRootAndPath($root, $path.'.'.$name);
            }
        }

        if (isset($this->data['embeddeds_many'])) {
            foreach ($this->data['embeddeds_many'] as $name => $embedded) {
                $embedded->setRootAndPath($root, $path.'.'.$name);
            }
        }
    }

    /**
     * Returns the root and path of the embedded document.
     *
     * @return array An array with the root and path (root and path keys) or null if they do not exist.
     */
    public function getRootAndPath()
    {
        return Archive::getOrDefault($this, 'root_and_path', null);
    }

    /**
     * Returns if the embedded document is an embedded one document changed.
     *
     * @return bool If the document is an embedded one document changed.
     */
    public function isEmbeddedOneChangedInParent()
    {
        if (!$rap = $this->getRootAndPath()) {
            return false;
        }

        if ($rap['root'] instanceof EmbeddedGroup) {
            return false;
        }

        $exPath = explode('.', $rap['path']);
        unset($exPath[count($exPath) -1 ]);

        $parentDocument = $rap['root'];
        foreach ($exPath as $embedded) {
            $parentDocument = $parentDocument->{'get'.\Mandango\Inflector::camelize($embedded)}();
            if ($parentDocument instanceof EmbeddedGroup) {
                return false;
            }
        }

        $rap = $this->getRootAndPath();
        $exPath = explode('.', $rap['path']);
        $name = $exPath[count($exPath) - 1];

        return $parentDocument->isEmbeddedOneChanged($name);
    }
}
