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

/**
 * PolymorphicReferenceGroup.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class PolymorphicReferenceGroup extends PolymorphicGroup
{
    /**
     * Constructor.
     *
     * @param string                             $discriminatorField The discriminator field.
     * @param Mandango\Document\AbstractDocument $parent             The parent document.
     * @param string                             $field              The reference field.
     */
    public function __construct($discriminatorField, $parent, $field)
    {
        parent::__construct($discriminatorField);

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
        return (array) $this->getParent()->get($this->getField());
    }

    /**
     * {@inheritdoc}
     */
    protected function doInitializeSaved(array $data)
    {
        $discriminatorField = $this->getDiscriminatorField();

        $ids = array();
        foreach ($data as $datum) {
            $ids[$datum[$discriminatorField]][] = $datum['id'];
        }

        $documents = array();
        foreach ($ids as $documentClass => $documentClassIds) {
            foreach ((array) call_user_func(array($documentClass, 'find'), $documentClassIds) as $document) {
                $documents[] = $document;
            }
        }

        return $documents;
    }
}
