<?php

namespace Model;

/**
 * Model\Element document.
 */
class Element extends \Model\Base\Element
{
    protected $events = array();
    protected $eventPrefix;

    public function getEvents()
    {
        return $this->events;
    }

    public function clearEvents()
    {
        $this->events = array();

        return $this;
    }

    public function setEventPrefix($prefix)
    {
        $this->eventPrefix = $prefix;

        return $this;
    }

    public function getEventPrefix()
    {
        return $this->eventPrefix;
    }

    protected function elementPreInsert()
    {
        $this->events[] = $this->eventPrefix.'ElementPreInserting';
    }

    protected function elementPostInsert()
    {
        $this->events[] = $this->eventPrefix.'ElementPostInserting';
    }

    protected function elementPreUpdate()
    {
        $this->events[] = $this->eventPrefix.'ElementPreUpdating';
    }

    protected function elementPostUpdate()
    {
        $this->events[] = $this->eventPrefix.'ElementPostUpdating';
    }

    protected function elementPreDelete()
    {
        $this->events[] = $this->eventPrefix.'ElementPreDeleting';
    }

    protected function elementPostDelete()
    {
        $this->events[] = $this->eventPrefix.'ElementPostDeleting';
    }
}
