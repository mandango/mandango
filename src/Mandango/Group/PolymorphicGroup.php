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
 * PolymorphicGroup.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
abstract class PolymorphicGroup extends AbstractGroup
{
    /**
     * Constructor.
     *
     * @param string $discriminatorField The discriminator field.
     */
    public function __construct($discriminatorField)
    {
        Archive::set($this, 'discriminatorField', $discriminatorField);
    }

    /**
     * Returns the discriminator field.
     *
     * @return string The discriminator field.
     */
    public function getDiscriminatorField()
    {
        return Archive::get($this, 'discriminatorField');
    }
}
