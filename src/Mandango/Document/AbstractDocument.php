<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Document;

use Mandango\Archive;
use Mandango\Mandango;

/**
 * The abstract class for documents.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
abstract class AbstractDocument
{
    private $mandango;

    protected $data = array();
    protected $fieldsModified = array();

    /**
     * Constructor.
     *
     * @param Mandango $mandango The mandango.
     */
    public function __construct(Mandango $mandango)
    {
        $this->mandango = $mandango;
    }

    /**
     * Destructor - empties the Archive cache
     */
    public function __destruct()
    {
        Archive::removeObject($this);
    }

    /**
     * Returns the mandango.
     *
     * @return Mandango The mandango.
     */
    public function getMandango()
    {
        return $this->mandango;
    }

    /**
     * Returns the document metadata.
     *
     * @return array The document metadata.
     */
    public function getMetadata()
    {
        return $this->getMandango()->getMetadataFactory()->getClass(get_class($this));
    }

    /**
     * Returns the document data.
     *
     * @return array The document data.
     */
    public function getDocumentData()
    {
        return $this->data;
    }

    /**
     * Returns if the document is modified.
     *
     * @return bool If the document is modified.
     *
     * @api
     */
    public function isModified()
    {
        if (isset($this->data['fields'])) {
            foreach ($this->data['fields'] as $name => $value) {
                if ($this->isFieldModified($name)) {
                    return true;
                }
            }
        }

        if (isset($this->data['embeddedsOne'])) {
            foreach ($this->data['embeddedsOne'] as $name => $embedded) {
                if ($embedded && $embedded->isModified()) {
                    return true;
                }
                if ($this->isEmbeddedOneChanged($name)) {
                    $root = null;
                    if ($this instanceof Document) {
                        $root = $this;
                    } elseif ($rap = $this->getRootAndPath()) {
                        $root = $rap['root'];
                    }
                    if ($root && !$root->isNew()) {
                        return true;
                    }
                }
            }
        }

        if (isset($this->data['embeddedsMany'])) {
            foreach ($this->data['embeddedsMany'] as $name => $group) {
                foreach ($group->getAdd() as $document) {
                    if ($document->isModified()) {
                        return true;
                    }
                }
                $root = null;
                if ($this instanceof Document) {
                    $root = $this;
                } elseif ($rap = $this->getRootAndPath()) {
                    $root = $rap['root'];
                }
                if ($root && !$root->isNew()) {
                    if ($group->getRemove()) {
                        return true;
                    }
                }
                if ($group->isSavedInitialized()) {
                    foreach ($group->getSaved() as $document) {
                        if ($document->isModified()) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Clear the document modifications, that is, they will not be modifications apart from here.
     *
     * @api
     */
    public function clearModified()
    {
        if (isset($this->data['fields'])) {
            $this->clearFieldsModified();
        }

        if (isset($this->data['embeddedsOne'])) {
            $this->clearEmbeddedsOneChanged();
            foreach ($this->data['embeddedsOne'] as $name => $embedded) {
                if ($embedded) {
                    $embedded->clearModified();
                }
            }
        }

        if (isset($this->data['embeddedsMany'])) {
            foreach ($this->data['embeddedsMany'] as $name => $group) {
                $group->clearAdd();
                $group->clearRemove();
                $group->clearSaved();
            }
        }
    }

    /**
     * Returns if a field is modified.
     *
     * @param string $name The field name.
     *
     * @return bool If the field is modified.
     *
     * @api
     */
    public function isFieldModified($name)
    {
        return isset($this->fieldsModified[$name]) || array_key_exists($name, $this->fieldsModified);
    }

    /**
     * Returns the original value of a field.
     *
     * @param string $name The field name.
     *
     * @return mixed The original value of the field.
     *
     * @api
     */
    public function getOriginalFieldValue($name)
    {
        if ($this->isFieldModified($name)) {
            return $this->fieldsModified[$name];
        }

        if (isset($this->data['fields'][$name])) {
            return $this->data['fields'][$name];
        }

        return null;
    }

    /**
     * Returns an array with the fields modified, the field name as key and the original value as value.
     *
     * @return array An array with the fields modified.
     *
     * @api
     */
    public function getFieldsModified()
    {
        return $this->fieldsModified;
    }

    /**
     * Clear the modifications of fields, that is, they will not be modifications apart from here.
     *
     * @api
     */
    public function clearFieldsModified()
    {
        $this->fieldsModified = array();
    }

    /**
     * Returns if an embedded one is changed.
     *
     * @param string $name The embedded one name.
     *
     * @return bool If the embedded one is modified.
     *
     * @api
     */
    public function isEmbeddedOneChanged($name)
    {
        if (!isset($this->data['embeddedsOne'])) {
            return false;
        }

        if (!isset($this->data['embeddedsOne'][$name]) && !array_key_exists($name, $this->data['embeddedsOne'])) {
            return false;
        }

        return Archive::has($this, 'embedded_one.'.$name);
    }

    /**
     * Returns the original value of an embedded one.
     *
     * @param string $name The embedded one name.
     *
     * @return mixed The embedded one original value.
     *
     * @api
     */
    public function getOriginalEmbeddedOneValue($name)
    {
        if (Archive::has($this, 'embedded_one.'.$name)) {
            return Archive::get($this, 'embedded_one.'.$name);
        }

        if (isset($this->data['embeddedsOne'][$name])) {
            return $this->data['embeddedsOne'][$name];
        }

        return null;
    }

    /**
     * Returns an array with the embedded ones changed, with the embedded name as key and the original embedded value as value.
     *
     * @return array An array with the embedded ones changed.
     *
     * @api
     */
    public function getEmbeddedsOneChanged()
    {
        $embeddedsOneChanged = array();
        if (isset($this->data['embeddedsOne'])) {
            foreach ($this->data['embeddedsOne'] as $name => $embedded) {
                if ($this->isEmbeddedOneChanged($name)) {
                    $embeddedsOneChanged[$name] = $this->getOriginalEmbeddedOneValue($name);
                }
            }
        }

        return $embeddedsOneChanged;
    }

    /**
     * Clear the embedded ones changed, that is, they will not be changed apart from here.
     *
     * @api
     */
    public function clearEmbeddedsOneChanged()
    {
        if (isset($this->data['embeddedsOne'])) {
            foreach ($this->data['embeddedsOne'] as $name => $embedded) {
                Archive::remove($this, 'embedded_one.'.$name);
            }
        }
    }

    /**
     * Returns an array with the document info to debug.
     *
     * @return array An array with the document info.
     */
    public function debug()
    {
        $info = array();

        $metadata = $this->getMetadata();

        $referenceFields = array();
        foreach (array_merge($metadata['referencesOne'], $metadata['referencesMany']) as $name => $reference) {
            $referenceFields[] = $reference['field'];
        }

        // fields
        foreach ($metadata['fields'] as $name => $field) {
            if (in_array($name, $referenceFields)) {
                continue;
            }
            $info['fields'][$name] = $this->{'get'.ucfirst($name)}();
        }

        // referencesOne
        foreach ($metadata['referencesOne'] as $name => $referenceOne) {
            $info['referencesOne'][$name] = $this->{'get'.ucfirst($referenceOne['field'])}();
        }

        // referencesMany
        foreach ($metadata['referencesMany'] as $name => $referenceMany) {
            $info['referencesMany'][$name] = $this->{'get'.ucfirst($referenceMany['field'])}();
        }

        // embeddedsOne
        foreach ($metadata['embeddedsOne'] as $name => $embeddedOne) {
            $embedded = $this->{'get'.ucfirst($name)}();
            $info['embeddedsOne'][$name] = $embedded ? $embedded->debug() : null;
        }

        // embeddedsMany
        foreach ($metadata['embeddedsMany'] as $name => $embeddedMany) {
            $info['embeddedsMany'][$name] = array();
            foreach ($this->{'get'.ucfirst($name)}() as $key => $value) {
                $info['embeddedsMany'][$name][$key] = $value->debug();
            }
        }

        return $info;
    }
}
