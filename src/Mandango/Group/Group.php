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

namespace Mandango\Group;

use Mandango\Archive;
use Mandango\Document\Document;

/**
 * Group.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
abstract class Group extends AbstractGroup
{
    /**
     * Constructor.
     *
     * @param string $documentClass The document class.
     *
     * @api
     */
    public function __construct($documentClass)
    {
        Archive::set($this, 'document_class', $documentClass);
    }

    /**
     * Returns the document class.
     *
     * @api
     */
    public function getDocumentClass()
    {
        return Archive::get($this, 'document_class');
    }
}
