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

/**
 * ReferenceGroup.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
class ReferenceGroup extends Group
{
    /**
     * Constructor.
     *
     * @param string                              $documentClass The document class.
     * @param \Mandango\Document\AbstractDocument $parent The parent document.
     * @param string                              $field  The reference field.
     *
     * @api
     */
    public function __construct($documentClass, $parent, $field)
    {
        parent::__construct($documentClass);

        Archive::set($this, 'parent', $parent);
        Archive::set($this, 'field', $field);
    }

    /**
     * Returns the parent document.
     *
     * @return \Mandango\Document\AbstractDocument The parent document.
     *
     * @api
     */
    public function getParent()
    {
        return Archive::get($this, 'parent');
    }

    /**
     * Returns the reference field.
     *
     * @return string The reference field.
     *
     * @api
     */
    public function getField()
    {
        return Archive::get($this, 'field');
    }

    /**
     * {@inheritdoc}
     */
    protected function doInitializeSavedData()
    {
        return (array) $this->getParent()->{'get'.ucfirst($this->getField())}();
    }

    /**
     * {@inheritdoc}
     */
    protected function doInitializeSaved(array $data)
    {
        return $this->getParent()->getMandango()->getRepository($this->getDocumentClass())->findById($data);
    }

    /**
     * Creates and returns a query to query the referenced elements.
     *
     * @api
     */
    public function createQuery()
    {
        return $this->getParent()->getMandango()->getRepository($this->getDocumentClass())->createQuery(array(
            '_id' => array('$in' => $this->doInitializeSavedData()),
        ));
    }
}
