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
 * SerializedType.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
class SerializedType extends Type
{
    /**
     * {@inheritdoc}
     */
    public function toMongo($value)
    {
        return serialize($value);
    }

    /**
     * {@inheritdoc}
     */
    public function toPHP($value)
    {
        return unserialize($value);
    }

    /**
     * {@inheritdoc}
     */
    public function toMongoInString()
    {
        return '%to% = serialize(%from%);';
    }

    /**
     * {@inheritdoc}
     */
    public function toPHPInString()
    {
        return '%to% = unserialize(%from%);';
    }
}
