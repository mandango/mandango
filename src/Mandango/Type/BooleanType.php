<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Type;

/**
 * BooleanType.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
class BooleanType extends Type
{
    /**
     * {@inheritdoc}
     */
    public function toMongo($value)
    {
        return (bool) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function toPHP($value)
    {
        return (bool) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function toMongoInString()
    {
        return '%to% = (bool) %from%;';
    }

    /**
     * {@inheritdoc}
     */
    public function toPHPInString()
    {
        return '%to% = (bool) %from%;';
    }
}
