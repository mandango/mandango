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
 * Class to load data from an array..
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class DataLoader
{
    private $mandango;

    /**
     * Constructor.
     *
     * @param \Mandango\Mandango $mandango The mandango.
     */
    public function __construct(Mandango $mandango)
    {
        $this->setMandango($mandango);
    }

    /**
     * Set the mandango.
     *
     * @param \Mandango\Mandango $mandango The mandango.
     */
    public function setMandango(Mandango $mandango)
    {
        $this->mandango = $mandango;
    }

    /**
     * Returns the Mandango.
     *
     * @return \Mandango\Mandango The Mandango.
     */
    public function getMandango()
    {
        return $this->mandango;
    }

    /**
     * Load data.
     *
     * @param array $data  The data to load.
     * @param bool  $purge If purge the databases before load the data.
     *
     * @throws \RuntimeException If the mandango's UnitOfWork has pending operations.
     */
    public function load(array $data, $purge = false)
    {
        // has pending
        if ($this->mandango->getUnitOfWork()->hasPending()) {
            throw new \RuntimeException('The mandango\'s Unit of Work has pending operations.');
        }

        // purge
        if ($purge) {
            foreach ($this->mandango->getAllRepositories() as $repository) {
                $repository->getCollection()->drop();
            }
        }

        // vars
        $mandango = $this->mandango;
        $documents = array();

        $maps = array();
        foreach ($data as $class => $datum) {
            $maps[$class] = $mandango->getRepository($class)->getMetadata();
        }

        $referencesOne = array();
        $referencesMany = array();
        foreach ($maps as $class => $metadata) {
            $referencesOne[$class] = $metadata['referencesOne'];
            $referencesMany[$class] = $metadata['referencesMany'];

            $map = $metadata;
            while ($map['inheritance']) {
                $inheritanceClass = $map['inheritance']['class'];
                $map = $mandango->getRepository($inheritanceClass)->getMetadata();
                $referencesOne[$class] = array_merge($map['referencesOne'], $referencesOne[$class]);
                $referencesMany[$class] = array_merge($map['referencesMany'], $referencesMany[$class]);
            }
        }

        // process function
        $process = function ($class, $key) use (&$process, $mandango, &$data, &$documents, &$maps, &$referencesOne, &$referencesMany) {
            static $processed = array();

            if (isset($processed[$class][$key])) {
                return;
            }

            if (!isset($data[$class][$key])) {
                throw new \RuntimeException(sprintf('The document "%s" of the class "%s" does not exist.', $key, $class));
            }
            $datum = $data[$class][$key];

            $documents[$class][$key] = $document = new $class($mandango);

            // referencesOne
            foreach ($referencesOne[$class] as $name => $reference) {
                if (!isset($datum[$name])) {
                    continue;
                }

                $process($reference['class'], $datum[$name]);

                if (!isset($documents[$reference['class']][$datum[$name]])) {
                    throw new \RuntimeException(sprintf('The reference "%s" (%s) for the class "%s" does not exists.', $datum[$name], $name, $class));
                }
                $document->set($name, $documents[$reference['class']][$datum[$name]]);
                unset($datum[$name]);
            }

            // referencesMany
            foreach ($referencesMany[$class] as $name => $reference) {
                if (!isset($datum[$name])) {
                    continue;
                }

                $refs = array();
                foreach ($datum[$name] as $value) {
                    $process($reference['class'], $value);

                    if (!isset($documents[$reference['class']][$value])) {
                        throw new \RuntimeException(sprintf('The reference "%s" (%s) for the class "%s" does not exists.', $value, $name, $class));
                    }
                    $refs[] = $documents[$reference['class']][$value];
                }
                $document->get($name)->add($refs);
                unset($datum[$name]);
            }

            // document
            $document->fromArray($datum);
            $mandango->persist($document);

            $processed[$class][$key] = true;
            unset($data[$class][$key]);
        };

        // process
        foreach ($data as $class => $datum) {
            foreach ($datum as $key => $value) {
                $process($class, $key);
            }
        }

        // flush
        $this->mandango->flush();
    }
}
