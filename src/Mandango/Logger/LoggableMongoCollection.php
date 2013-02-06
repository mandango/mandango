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
 * A loggable MongoCollection.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class LoggableMongoCollection extends \MongoCollection
{
    private $db;
    private $time;

    /**
     * Constructor.
     *
     * @param \Mandango\Logger\LoggableMongoDB $db             A LoggableMongoDB instance.
     * @param string                           $collectionName The collection name.
     */
    public function __construct(LoggableMongoDB $db, $collectionName)
    {
        $this->db = $db;
        $this->time = new Time();

        parent::__construct($db, $collectionName);
    }

    /**
     * Returns the LoggableMongoDB.
     *
     * @return \Mandango\Logger\LoggableMongoDB The LoggableMongoDB
     */
    public function getDB()
    {
        return $this->db;
    }

    /**
     * Log.
     *
     * @param array $log The log.
     */
    public function log(array $log)
    {
        $this->db->log(array_merge(array(
            'collection' => $this->getName()
        ), $log));
    }

    /**
     * batchInsert.
     */
/*
    FIXME: 1.2 and 1.3 versions of the php mongo driver differ

    public function batchInsert(array $a, array $options = array())
    {
        $this->time->start();
        $return = parent::batchInsert($a, $options);
        $time = $this->time->stop();

        $this->log(array(
            'type'    => 'batchInsert',
            'nb'      => count($a),
            'data'    => $a,
            'options' => $options,
            'time'    => $time,
        ));

        return $return;
    }
*/

    /**
     * count.
     */
    public function count($query = array(), $limit = 0, $skip = 0)
    {
        $this->time->start();
        $return = parent::count($query, $limit, $skip);
        $time = $this->time->stop();

        $this->log(array(
            'type'  => 'count',
            'query' => $query,
            'limit' => $limit,
            'skip'  => $skip,
            'time'  => $time,
        ));

        return $return;
    }

    /**
     * deleteIndex.
     */
    public function deleteIndex($keys)
    {
        $this->time->start();
        $return = parent::deleteIndex($keys);
        $time = $this->time->stop();

        $this->log(array(
            'type' => 'deleteIndex',
            'keys' => $keys,
            'time' => $time,
        ));

        return $return;
    }

    /**
     * deleteIndexes.
     */
    public function deleteIndexes()
    {
        $this->time->start();
        $return = parent::deleteIndexes();
        $time = $this->time->stop();

        $this->log(array(
            'type' => 'deleteIndexes',
            'time' => $time,
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
     * ensureIndex.
     */
    public function ensureIndex($keys, array $options = array())
    {
        $this->time->start();
        $return = parent::ensureIndex($keys, $options);
        $time = $this->time->stop();

        $this->log(array(
            'type'    => 'ensureIndex',
            'keys'    => $keys,
            'options' => $options,
            'time'    => $time,
        ));

        return $return;
    }

    /**
     * find.
     */
    public function find($query = array(), $fields = array())
    {
        return new LoggableMongoCursor($this, $query, $fields);
    }

    /**
     * findOne.
     */
    public function findOne($query = array(), $fields = array())
    {
        $cursor = new LoggableMongoCursor($this, $query, $fields, 'findOne');
        $cursor->limit(-1);

        return $cursor->getNext();
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
     * getIndexInfo.
     */
    public function getIndexInfo()
    {
        $this->time->start();
        $return = parent::getIndexInfo();
        $time = $this->time->stop();

        $this->log(array(
            'type' => 'getIndexInfo',
            'time' => $time,
        ));

        return $return;
    }

    /**
     * group.
     */
    public function group($keys, $initial, $reduce, array $options = array())
    {
        $this->time->start();
        $return = parent::group($keys, $initial, $reduce, $options);
        $time = $this->time->stop();

        $this->log(array(
            'type'    => 'group',
            'keys'    => $keys,
            'initial' => $initial,
            'reduce'  => $reduce,
            'options' => $options,
            'time'    => $time,
        ));

        return $return;
    }

    /**
     * insert.
     */
    public function insert($a, array $options = array())
    {
        $this->time->start();
        $return = parent::insert($a, $options);
        $time = $this->time->stop();

        $this->log(array(
            'type'    => 'insert',
            'a'       => $a,
            'options' => $options,
            'time'    => $time,
        ));

        return $return;
    }

    /**
     * remove.
     */
    public function remove($criteria = array(), array $options = array())
    {
        $this->time->start();
        $return = parent::remove($criteria, $options);
        $time = $this->time->stop();

        $this->log(array(
            'type'     => 'remove',
            'criteria' => $criteria,
            'options'  => $options,
            'time'     => $time,
        ));

        return $return;
    }

    /**
     * save.
     */
    public function save($a, array $options = array())
    {
        $this->time->start();
        $return = parent::save($a, $options);
        $time = $this->time->stop();

        $this->log(array(
            'type'    => 'save',
            'a'       => $a,
            'options' => $options,
            'time'    => $time,
        ));

        return $return;
    }

    /**
     * update.
     */
    public function update($criteria, $newobj, array $options = array())
    {
        $this->time->start();
        $return = parent::update($criteria, $newobj, $options);
        $time = $this->time->stop();

        $this->log(array(
            'type'     => 'update',
            'criteria' => $criteria,
            'newobj'   => $newobj,
            'options'  => $options,
            'time'     => $time,
        ));

        return $return;
    }

    /**
     * validate.
     */
    public function validate($scanData = false)
    {
        $this->time->start();
        $return = parent::validate($scanData);
        $time = $this->time->stop();

        $this->log(array(
            'type'     => 'validate',
            'scanData' => $scanData,
            'time'     => $time,
        ));

        return $return;
    }
}
