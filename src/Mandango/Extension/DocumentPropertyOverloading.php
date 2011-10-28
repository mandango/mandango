<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Extension;

use Mandango\Mondator\Definition\Method;
use Mandango\Mondator\Extension;

/**
 * DocumentPropertyOverloading extension.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class DocumentPropertyOverloading extends Extension
{
    /**
     * {@inheritdoc}
     */
    protected function doClassProcess()
    {
        $this->processTemplate($this->definitions['document_base'], file_get_contents(__DIR__.'/templates/DocumentPropertyOverloading.php.twig'));
    }
}
