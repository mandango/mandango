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

/**
 * PolymorphicGroup.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
abstract class PolymorphicGroup extends AbstractGroup
{
    /**
     * Constructor.
     *
     * @param string $discriminatorField The discriminator field.
     *
     * @api
     */
    public function __construct($discriminatorField)
    {
        Archive::set($this, 'discriminatorField', $discriminatorField);
    }

    /**
     * Returns the discriminator field.
     *
     * @return string The discriminator field.
     *
     * @api
     */
    public function getDiscriminatorField()
    {
        return Archive::get($this, 'discriminatorField');
    }
}
