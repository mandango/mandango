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
use Mandango\Inflector;

/**
 * ReferenceGroup.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class ReferenceGroup extends Group
{
    /**
     * Constructor.
     *
     * @param string                             $documentClass The document class.
     * @param Mandango\Document\AbstractDocument $parent The parent document.
     * @param string                             $field  The reference field.
     */
    public function __construct($documentClass, $parent, $field)
    {
        parent::__construct($documentClass);

        Archive::set($this, 'parent', $parent);
        Archive::set($this, 'field', $field);
    }

    /**
     * Returns the parent document.
     *
     * @return Mandango\Document\AbstractDocument The parent document.
     */
    public function getParent()
    {
        return Archive::get($this, 'parent');
    }

    /**
     * Returns the reference field.
     *
     * @return string The reference field.
     */
    public function getField()
    {
        return Archive::get($this, 'field');
    }

    /**
     * {@inheritdoc}
     */
    protected function doInitializeSavedData()
    {
        return (array) $this->getParent()->{'get'.Inflector::camelize($this->getField())}();
    }

    /**
     * {@inheritdoc}
     */
    protected function doInitializeSaved(array $data)
    {
        return call_user_func(array($this->getDocumentClass(), 'repository'))->find($data);
    }
}
