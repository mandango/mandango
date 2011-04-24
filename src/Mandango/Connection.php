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
