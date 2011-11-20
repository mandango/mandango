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
 * Does not generate anything.
 *
 * You can put your own identifiers or rely on Mongo.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class NoneIdGenerator extends BaseIdGenerator
{
    /**
     * {@inheritdoc}
     */
    public function getCode(array $options)
    {
        return <<<EOF
if (null !== \$document->getId()) {
    %id% = \$document->getId();
}
EOF;
    }

    /**
     * {@inheritdoc}
     */
    public function getToMongoCode()
    {
        return '';
    }
}
