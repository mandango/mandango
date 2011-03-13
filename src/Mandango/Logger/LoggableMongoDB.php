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

namespace Mandango\Logger;

/**
 * A loggable MongoDB.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class LoggableMongoDB extends \MongoDB
{
    protected $mongo;

    protected $time;

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
    public function command($data)
    {
        $this->time->start();
        $return = parent::command($data);
        $time = $this->time->stop();

        $this->log(array(
            'type' => 'command',
            'data' => $data,
            'time' => $time,
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
    public function listCollections()
    {
        $this->time->start();
        $return = parent::listCollections();
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
