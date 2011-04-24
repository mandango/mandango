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
 * A loggable Mongo.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class LoggableMongo extends \Mongo
{
    private $loggerCallable;
    private $logDefault = array();

    /**
     * Set the logger callable.
     *
     * @param mixed $loggerCallable A PHP callable.
     */
    public function setLoggerCallable($loggerCallable)
    {
        $this->loggerCallable = $loggerCallable;
    }

    /**
     * Returns the logger callable.
     *
     * @return mixed The logger callable.
     */
    public function getLoggerCallable()
    {
        return $this->loggerCallable;
    }

    /**
     * Set the log default.
     *
     * @param array $logDefault The log default.
     */
    public function setLogDefault(array $logDefault)
    {
        $this->logDefault = $logDefault;
    }

    /**
     * Returns the log default.
     *
     * @return array The log default.
     */
    public function getLogDefault()
    {
        return $this->logDefault;
    }

    /**
     * Log.
     *
     * @param array $log The log value.
     */
    public function log(array $log)
    {
        if ($this->loggerCallable) {
            call_user_func($this->loggerCallable, array_merge($this->logDefault, $log));
        }
    }

    /**
     * selectDB.
     */
    public function selectDB($name)
    {
        return new LoggableMongoDB($this, $name);
    }

    /**
     * __get.
     */
    public function __get($dbname)
    {
        return $this->selectDB($dbname);
    }
}
