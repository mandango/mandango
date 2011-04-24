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

namespace Mandango;

use Mandango\Document\Document;

/**
 * The identity map class.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
class IdentityMap implements IdentityMapInterface
{
    private $documents;

    /**
     * Constructor.
     *
     * @api
     */
    public function __construct()
    {
        $this->documents = array();
    }

    /**
     * {@inheritdoc}
     */
    public function set($id, Document $document)
    {
        $this->documents[(string) $id] = $document;
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return isset($this->documents[(string) $id]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        return $this->documents[(string) $id];
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->documents;
    }

    /**
     * {@inheritdoc}
     */
    public function &allByReference()
    {
        return $this->documents;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($id)
    {
        unset($this->documents[(string) $id]);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->documents = array();
    }
}
