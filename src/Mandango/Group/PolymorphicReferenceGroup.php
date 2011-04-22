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
     * @param array|false                        $discriminatorMap   The discriminator map if exists, otherwise false.
     */
    public function __construct($discriminatorField, $parent, $field, $discriminatorMap = false)
    {
        parent::__construct($discriminatorField);

        Archive::set($this, 'parent', $parent);
        Archive::set($this, 'field', $field);
        Archive::set($this, 'discriminator_map', $discriminatorMap);
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
     * Returns the discriminator map.
     *
     * @return array|false The discriminator map.
     */
    public function getDiscriminatorMap()
    {
        return Archive::get($this, 'discriminator_map');
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
        $discriminatorMap = $this->getDiscriminatorMap();

        $ids = array();
        foreach ($data as $datum) {
            if ($discriminatorMap) {
                $documentClass = $discriminatorMap[$datum[$discriminatorField]];
            } else {
                $documentClass = $datum[$discriminatorField];
            }
            $ids[$documentClass][] = $datum['id'];
        }

        $documents = array();
        foreach ($ids as $documentClass => $documentClassIds) {
            foreach ((array) call_user_func(array($documentClass, 'getRepository'))->findById($documentClassIds) as $document) {
                $documents[] = $document;
            }
        }

        return $documents;
    }
}
