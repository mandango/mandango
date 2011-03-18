<?php

/*
 * Copyright 2010 Pablo Díez <pablodip@gmail.com>
 *
 * This file is part of Mandango.
 *
 * Mandango is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Mandango is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Mandango. If not, see <http://www.gnu.org/licenses/>.
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
    protected function offsetExistsMethodProcess()
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
    protected function offsetSetMethodProcess()
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
    protected function offsetGetMethodProcess()
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
    protected function offsetUnsetMethodProcess()
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
