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

namespace Mandango;

/**
 * Connection.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
class Connection implements ConnectionInterface
{
    private $server;
    private $dbName;
    private $options;

    private $loggerCallable;
    private $logDefault;

    private $mongo;
    private $mongoDB;

    /**
     * Constructor.
     *
     * @param string $server  The server.
     * @param string $dbName  The database name.
     * @param array  $options The \Mongo options (optional).
     *
     * @api
     */
    public function __construct($server, $dbName, array $options = array())
    {
        $this->server = $server;
        $this->dbName = $dbName;
        $this->options = $options;
    }

    /**
     * Returns the server.
     *
     * @return string $server The server.
     *
     * @api
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Returns the database name.
     *
     * @return string The database name.
     *
     * @api
     */
    public function getDbName()
    {
        return $this->dbName;
    }

    /**
     * Returns the options.
     *
     * @return array The options.
     *
     * @api
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function setLoggerCallable($loggerCallable = null)
    {
        if (null !== $this->mongo) {
            throw new \RuntimeException('The connection has already Mongo.');
        }

        $this->loggerCallable = $loggerCallable;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoggerCallable()
    {
        return $this->loggerCallable;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogDefault(array $logDefault)
    {
        if (null !== $this->mongo) {
            throw new \RuntimeException('The connection has already Mongo.');
        }

        $this->logDefault = $logDefault;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDefault()
    {
        return $this->logDefault;
    }

    /**
     * {@inheritdoc}
     */
    public function getMongo()
    {
        if (null === $this->mongo) {
            if (null !== $this->loggerCallable) {
                $this->mongo = new \Mandango\Logger\LoggableMongo($this->server, $this->options);
                $this->mongo->setLoggerCallable($this->loggerCallable);
                if (null !== $this->logDefault) {
                    $this->mongo->setLogDefault($this->logDefault);
                }
            } else {
                $this->mongo = new \Mongo($this->server, $this->options);
            }
        }

        return $this->mongo;
    }

    /**
     * {@inheritdoc}
     */
    public function getMongoDB()
    {
        if (null === $this->mongoDB) {
            $this->mongoDB = $this->getMongo()->selectDB($this->dbName);
        }

        return $this->mongoDB;
    }
}
