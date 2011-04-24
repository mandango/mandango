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
 * DocumentArrayAccess extension.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class DocumentArrayAccess extends Extension
{
    /**
     * {@inheritdoc}
     */
    protected function doClassProcess()
    {
        $this->definitions['document_base']->addInterface('\ArrayAccess');

        $this->offsetExistsMethodProcess();
        $this->offsetSetMethodProcess();
        $this->offsetGetMethodProcess();
        $this->offsetUnsetMethodProcess();
    }

    /*
     * "offsetExists" method
     */
    private function offsetExistsMethodProcess()
    {
        $method = new Method('public', 'offsetExists', '$name', <<<EOF
        throw new \LogicException('You cannot check if data exists.');
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Throws an \LogicException because you cannot check if data exists.
     *
     * @throws \LogicException
     */
EOF
        );

        $this->definitions['document_base']->addMethod($method);
    }

    /*
     * "offsetSet" method
     */
    private function offsetSetMethodProcess()
    {
        $method = new Method('public', 'offsetSet', '$name, $value', <<<EOF
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
     * @throws \InvalidArgumentException If the data name does not exists.
     */
EOF
        );

        $this->definitions['document_base']->addMethod($method);
    }

    /*
     * "offsetGet" method
     */
    private function offsetGetMethodProcess()
    {
        $method = new Method('public', 'offsetGet', '$name', <<<EOF
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

    /*
     * "offsetUnset" method
     */
    private function offsetUnsetMethodProcess()
    {
        $method = new Method('public', 'offsetUnset', '$name', <<<EOF
        throw new \LogicException('You cannot unset data.');
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Throws a \LogicException because you cannot unset data through ArrayAccess.
     *
     * @throws \LogicException
     */
EOF
        );

        $this->definitions['document_base']->addMethod($method);
    }
}
