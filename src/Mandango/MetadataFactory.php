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

/**
 * Metadata.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
abstract class MetadataFactory
{
    /*
     * abstract
     * protected $classes = array();
     *
     * Array of document classes. The class as key and if is embedded as value.
     */

    private $infoClass;

    /**
     * Returns the classes.
     *
     * @return array The classes.
     */
    public function getClasses()
    {
        return array_keys($this->classes);
    }

    /**
     * Returns the classes of documents (not embeddeds).
     *
     * @return array The classes of documents.
     */
    public function getDocumentClasses()
    {
        $classes = array();
        foreach ($this->classes as $class => $isEmbedded) {
            if (!$isEmbedded) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    /**
     * Returns the classes of embeddeds documents.
     *
     * @return array The classes of embeddeds documents.
     */
    public function getEmbeddedDocumentClasses()
    {
        $classes = array();
        foreach ($this->classes as $class => $isEmbedded) {
            if ($isEmbedded) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    /**
     * Returns if a class exists.
     *
     * @param string $class The class.
     *
     * @return bool Returns if a class exists.
     */
    public function hasClass($class)
    {
        return isset($this->classes[$class]);
    }

    /**
     * Returns if a class is a document (not embedded).
     *
     * @param string $class A class.
     *
     * @return bool If the class is a document (not embedded).
     *
     * @throws \LogicException If the class does not exist in the metadata.
     */
    public function isDocumentClass($class)
    {
        $this->checkClass($class);

        return !$this->classes[$class];
    }

    /**
     * Returns if a class is a embedded document.
     *
     * @param string $class A class.
     *
     * @return bool If the class is a embedded document.
     *
     * @throws \LogicException If the class does not exist in the metadata.
     */
    public function isEmbeddedDocumentClass($class)
    {
        $this->checkClass($class);

        return $this->classes[$class];
    }

    /**
     * Returns the metadata of a class.
     *
     * @param string $class The class.
     *
     * @return array The metadata of the class.
     *
     * @throws \LogicException If the class does not exist in the metadata factory.
     */
    public function getClass($class)
    {
        $this->checkClass($class);

        if (null === $this->infoClass) {
            $infoClass = get_class($this).'Info';
            $this->infoClass = new $infoClass();
        }

        return $this->infoClass->{'get'.str_replace('\\', '', $class).'Class'}();
    }

    protected function checkClass($class)
    {
        if (!$this->hasClass($class)) {
            throw new \LogicException(sprintf('The class "%s" does not exist in the metadata factory.', $class));
        }
    }
}
