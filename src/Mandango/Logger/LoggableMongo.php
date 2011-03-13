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
 * A loggable Mongo.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class LoggableMongo extends \Mongo
{
    protected $loggerCallable;

    protected $logDefault = array();

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
