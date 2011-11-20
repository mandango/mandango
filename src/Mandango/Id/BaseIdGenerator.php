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
 * BaseIdGenerator.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
abstract class BaseIdGenerator
{
    /**
     * Returns the code to generate the id.
     *
     * @param array $options An array of options.
     *
     * @return string The code to generate.
     */
    abstract public function getCode(array $options);

    /**
     * Returns the code to convert an id to the mongo value.
     */
    abstract public function getToMongoCode();
}
