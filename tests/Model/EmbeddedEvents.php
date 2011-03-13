<?php

namespace Model;

/**
 * Model\EmbeddedEvents document.
 */
class EmbeddedEvents extends \Model\Base\EmbeddedEvents
{
    protected $events = array();
    protected $myEventPrefix;

    public function getEvents()
    {
        return $this->events;
    }

    public function clearEvents()
    {
        $this->events = array();

        return $this;
    }

    public function setMyEventPrefix($prefix)
    {
        $this->myEventPrefix = $prefix;

        return $this;
    }

    public function getMyEventPrefix()
    {
        return $this->myEventPrefix;
    }

    protected function myPreInsert()
    {
        $this->events[] = $this->myEventPrefix.'PreInserting';
    }

    protected function myPostInsert()
    {
        $this->events[] = $this->myEventPrefix.'PostInserting';
    }

    protected function myPreUpdate()
    {
        $this->events[] = $this->myEventPrefix.'PreUpdating';
    }

    protected function myPostUpdate()
    {
        $this->events[] = $this->myEventPrefix.'PostUpdating';
    }

    protected function myPreDelete()
    {
        $this->events[] = $this->myEventPrefix.'PreDeleting';
    }

    protected function myPostDelete()
    {
        $this->events[] = $this->myEventPrefix.'PostDeleting';
    }
}
