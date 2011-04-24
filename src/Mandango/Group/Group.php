<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
