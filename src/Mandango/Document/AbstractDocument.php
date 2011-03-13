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

namespace Mandango\Document;

use Mandango\Archive;

/**
 * The abstract class for documents.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
abstract class AbstractDocument
{
    protected $data = array();
    protected $fieldsModified = array();

    static public function create()
    {
        return new static();
    }

    /*
     * Returns the mandango of the document.
     *
     * @return Mandango\Mandango The mandango of the document.
     *
     * abstract static public function mandango();
     */

    /**
     * Returns the metadata info of the class.
     *
     * @return array The metadata info of the class.
     */
    static public function metadata()
    {
        return static::mandango()->getMetadata()->getClassInfo(get_called_class());
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

        if (isset($this->data['embeddeds_one'])) {
            foreach ($this->data['embeddeds_one'] as $name => $embedded) {
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

        if (isset($this->data['embeddeds_many'])) {
            foreach ($this->data['embeddeds_many'] as $name => $group) {
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
                    foreach ($group->saved() as $document) {
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
     */
    public function clearModified()
    {
        if (isset($this->data['fields'])) {
            $this->clearFieldsModified();
        }

        if (isset($this->data['embeddeds_one'])) {
            $this->clearEmbeddedsOneChanged();
            foreach ($this->data['embeddeds_one'] as $name => $embedded) {
                if ($embedded) {
                    $embedded->clearModified();
                }
            }
        }

        if (isset($this->data['embeddeds_many'])) {
            foreach ($this->data['embeddeds_many'] as $name => $group) {
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
     */
    public function getFieldsModified()
    {
        return $this->fieldsModified;
    }

    /**
     * Clear the modifications of fields, that is, they will not be modifications apart from here.
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
     */
    public function isEmbeddedOneChanged($name)
    {
        if (!isset($this->data['embeddeds_one'])) {
            return false;
        }

        if (!isset($this->data['embeddeds_one'][$name]) && !array_key_exists($name, $this->data['embeddeds_one'])) {
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
     */
    public function getOriginalEmbeddedOneValue($name)
    {
        if (Archive::has($this, 'embedded_one.'.$name)) {
            return Archive::get($this, 'embedded_one.'.$name);
        }

        if (isset($this->data['embeddeds_one'][$name])) {
            return $this->data['embeddeds_one'][$name];
        }

        return null;
    }

    /**
     * Returns an array with the embedded ones changed, with the embedded name as key and the original embedded value as value.
     *
     * @return array An array with the embedded ones changed.
     */
    public function getEmbeddedsOneChanged()
    {
        $embeddedsOneChanged = array();
        if (isset($this->data['embeddeds_one'])) {
            foreach ($this->data['embeddeds_one'] as $name => $embedded) {
                if ($this->isEmbeddedOneChanged($name)) {
                    $embeddedsOneChanged[$name] = $this->getOriginalEmbeddedOneValue($name);
                }
            }
        }

        return $embeddedsOneChanged;
    }

    /**
     * Clear the embedded ones changed, that is, they will not be changed apart from here.
     */
    public function clearEmbeddedsOneChanged()
    {
        if (isset($this->data['embeddeds_one'])) {
            foreach ($this->data['embeddeds_one'] as $name => $embedded) {
                Archive::remove($this, 'embedded_one.'.$name);
            }
        }
    }
}
