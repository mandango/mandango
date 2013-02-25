<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Logger;

/**
 * A loggable MongoDB.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class LoggableMongoDB extends \MongoDB
{
    private $mongo;
    private $time;

    /**
     * Constructor.
     *
     * @param \Mandango\Logger\LoggableMongo $mongo A LoggableMongo instance.
     * @param string                         $name  The database name.
     */
    public function __construct(LoggableMongo $mongo, $name)
    {
        $this->mongo = $mongo;
        $this->time = new Time();

        return parent::__construct($mongo, $name);
    }

    /**
     * Returns the LoggableMongo.
     *
     * @return \Mandango\Logger\LoggableMongo The LoggableMongo.
     */
    public function getMongo()
    {
        return $this->mongo;
    }

    /**
     * Log.
     *
     * @param array $log The log.
     */
    public function log(array $log)
    {
        $this->mongo->log(array_merge(array(
            'database' => $this->__toString()
        ), $log));
    }

    /**
     * command.
     */
    public function command($command, array $options = array())
    {
        $this->time->start();
        $return = parent::command($command, $options);
        $time = $this->time->stop();

        $this->log(array(
            'type'    => 'command',
            'options' => $options,
            'time'    => $time,
        ));

        return $return;
    }

    /**
     * createCollection.
     */
    public function createCollection($name, $capped = false, $size = 0, $max = 0)
    {
        $this->time->start();
        $return = parent::createCollection($name, $capped, $size, $max);
        $time = $this->time->stop();

        $this->log(array(
            'type'   => 'createCollection',
            'name'   => $name,
            'capped' => $capped,
            'size'   => $size,
            'max'    => $max,
            'time'    => $time,
        ));

        return $return;
    }

    /**
     * createDbRef.
     */
    public function createDBRef($collection, $a)
    {
        $this->time->start();
        $return = parent::createDBRef($collection, $a);
        $time = $this->time->stop();

        $this->log(array(
            'type'       => 'createDBRef',
            'collection' => $collection,
            'a'          => $a,
            'time'       => $time,
        ));

        return $return;
    }

    /**
     * drop.
     */
    public function drop()
    {
        $this->time->start();
        $return = parent::drop();
        $time = $this->time->stop();

        $this->log(array(
            'type' => 'drop',
            'time' => $time,
        ));

        return $return;
    }

    /**
     * execute.
     */
    public function execute($code, array $args = array())
    {
        $this->time->start();
        $return = parent::execute($code, $args);
        $time = $this->time->stop();

        $this->log(array(
            'type' => 'execute',
            'code' => $code,
            'args' => $args,
            'time' => $time,
        ));

        return $return;
    }

    /**
     * getDBRef.
     */
    public function getDBRef($ref)
    {
        $this->time->start();
        $return = parent::getDBRef($ref);
        $time = $this->time->stop();

        $this->log(array(
            'type' => 'getDBRef',
            'ref'  => $ref,
            'time' => $time,
        ));

        return $return;
    }

    /**
     * listCollections.
     */
    public function listCollections($includeSystemCollections = false)
    {
        $this->time->start();
        $return = parent::listCollections($includeSystemCollections);
        $time = $this->time->stop();

        $this->log(array(
            'type' => 'listCollections',
            'time' => $time,
        ));

        return $return;
    }

    /**
     * selectCollection.
     */
    public function selectCollection($name)
    {
        return new LoggableMongoCollection($this, $name);
    }

    /**
     * __get.
     */
    public function __get($name)
    {
        return $this->selectCollection($name);
    }

    /*
     * getGridFS.
     */
    public function getGridFS($prefix = 'fs')
    {
        return new LoggableMongoGridFS($this, $prefix);
    }
}
