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
        $this->__setMethodProcess();
        $this->__getMethodProcess();
    }

    /*
     * "__set" method
     */
    private function __setMethodProcess()
    {
        $method = new Method('public', '__set', '$name, $value', <<<EOF
        \$this->set(\$name, \$value);
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Set data in the document.
     *
     * @param string \$name  The data name.
     * @param mixed  \$value The value.
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the data name does not exists.
     */
EOF
        );

        $this->definitions['document_base']->addMethod($method);
    }

    /*
     * "__get" method
     */
    private function __getMethodProcess()
    {
        $method = new Method('public', '__get', '$name', <<<EOF
        return \$this->get(\$name);
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns data of the document.
     *
     * @param string \$name The data name.
     *
     * @return mixed Some data.
     *
     * @throws \InvalidArgumentException If the data name does not exists.
     */
EOF
        );

        $this->definitions['document_base']->addMethod($method);
    }
}
