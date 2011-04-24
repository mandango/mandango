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
 * A loggable MongoGridFSCursor.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class LoggableMongoGridFSCursor extends \MongoGridFSCursor
{
    private $grid;
    private $type;
    private $explainCursor;
    private $time;

    /**
     * Constructor.
     */
    public function __construct(LoggableMongoGridFS $grid, array $query = array(), array $fields = array(), $type = 'find')
    {
        $this->grid = $grid;

        $mongo = $grid->getDB()->getMongo();
        $ns = $grid->getDB()->__toString().'.'.$grid->getName();

        $this->type = $type;
        $this->explainCursor = new \MongoGridFSCursor($grid, $mongo, $ns, $query, $fields);
        $this->time = new Time();

        parent::__construct($grid, $mongo, $ns, $query, $fields);
    }

    /**
     * Returns the LoggableMongoGridFS.
     *
     * @return \Mandango\Logger\LoggableMongoGridFS The LoggableMongoGridFS.
     */
    public function getGrid()
    {
        return $this->grid;
    }

    /**
     * Log.
     *
     * @param array $log The log.
     */
    public function log(array $log)
    {
        $this->grid->log($log);
    }

    /*
     * hasNext.
     */
    public function hasNext()
    {
        $this->logQuery();

        return parent::hasNext();
    }

    /*
     * getNext.
     */
    public function getNext()
    {
        $this->logQuery();

        return parent::getNext();
    }

    /*
     * rewind.
     */
    public function rewind()
    {
        $this->logQuery();

        return parent::rewind();
    }

    /*
     * next.
     */
    public function next()
    {
        $this->logQuery();

        return parent::next();
    }

    /*
     * count.
     */
    public function count($foundOnly = false)
    {
        $this->time->start();
        $return = parent::count($foundOnly);
        $time = $this->time->stop();

        $info = $this->info();

        $this->log(array(
            'type'      => 'count',
            'query'     => is_array($info['query']) ? $info['query'] : array(),
            'limit'     => $info['limit'],
            'skip'      => $info['skip'],
            'foundOnly' => $foundOnly,
            'time'      => $time,
        ));

        return $return;
    }

    /*
     * log the query.
     */
    protected function logQuery()
    {
        $info = $this->info();

        if (!$info['started_iterating']) {
            if (!is_array($info['query'])) {
                $info['query'] = array();
            } else if (!isset($info['query']['$query'])) {
                $info['query'] = array('$query' => $info['query']);
            }

            // explain cursor
            $this->explainCursor->fields($info['fields']);
            $this->explainCursor->limit($info['limit']);
            $this->explainCursor->skip($info['skip']);
            if (isset($info['batchSize'])) {
                $this->explainCursor->batchSize($info['batchSize']);
            }
            if (isset($info['query']['$orderby'])) {
                $this->explainCursor->sort($info['query']['$orderby']);
            }
            if (isset($info['query']['$hint'])) {
                $this->explainCursor->hint($info['query']['$hint']);
            }
            if (isset($info['query']['$snapshot'])) {
                $this->explainCursor->snapshot();
            }
            $explain = $this->explainCursor->explain();

            // log
            $log = array(
                'type'   => $this->type,
                'query'  => isset($info['query']['$query']) && is_array($info['query']['$query']) ? $info['query']['$query'] : array(),
                'fields' => $info['fields'],
            );
            if (isset($info['query']['$orderby'])) {
                $log['sort'] = $info['query']['$orderby'];
            }
            if ($info['limit']) {
                $log['limit'] = $info['limit'];
            }
            if ($info['skip']) {
                $log['skip'] = $info['skip'];
            }
            if ($info['batchSize']) {
                $log['batchSize'] = $info['batchSize'];
            }
            if (isset($info['query']['$hint'])) {
                $log['hint'] = $info['query']['$hint'];
            }
            if (isset($info['query']['$snapshot'])) {
                $log['snapshot'] = 1;
            }
            $log['explain'] = array(
                'nscanned'        => $explain['nscanned'],
                'nscannedObjects' => $explain['nscannedObjects'],
                'n'               => $explain['n'],
                'indexBounds'     => $explain['indexBounds'],
            );
            $log['time'] = $explain['millis'];

            $this->log($log);
        }
    }
}
