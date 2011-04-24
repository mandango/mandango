<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
