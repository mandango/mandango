<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Id;

use Mandango\Document\Document;

/**
 * Generates a native identifier.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class NativeIdGenerator extends BaseIdGenerator
{
    /**
     * {@inheritdoc}
     */
    public function getCode(array $options)
    {
        return '%id% = new \MongoId();';
    }

    /**
     * {@inheritdoc}
     */
    public function getToMongoCode()
    {
        return <<<EOF
if (!%id% instanceof \MongoId) {
    %id% = new \MongoId(%id%);
}
EOF;
    }
}
