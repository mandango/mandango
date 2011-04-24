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
 * StringType.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
class StringType extends Type
{
    /**
     * {@inheritdoc}
     */
    public function toMongo($value)
    {
        return (string) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function toPHP($value)
    {
        return (string) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function toMongoInString()
    {
        return '%to% = (string) %from%;';
    }

    /**
     * {@inheritdoc}
     */
    public function toPHPInString()
    {
        return '%to% = (string) %from%;';
    }
}
