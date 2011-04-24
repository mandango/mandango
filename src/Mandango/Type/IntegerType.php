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
 * IntegerType.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
class IntegerType extends Type
{
    /**
     * {@inheritdoc}
     */
    public function toMongo($value)
    {
        return (int) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function toPHP($value)
    {
        return (int) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function toMongoInString()
    {
        return '%to% = (int) %from%;';
    }

    /**
     * {@inheritdoc}
     */
    public function toPHPInString()
    {
        return '%to% = (int) %from%;';
    }
}
