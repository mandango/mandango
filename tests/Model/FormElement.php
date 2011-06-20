<?php

namespace Model;

/**
 * Model\FormElement document.
 */
class FormElement extends \Model\Base\FormElement
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

    protected function formElementPreInsert()
    {
        $this->events[] = $this->eventPrefix.'FormElementPreInserting';
    }

    protected function formElementPostInsert()
    {
        $this->events[] = $this->eventPrefix.'FormElementPostInserting';
    }

    protected function formElementPreUpdate()
    {
        $this->events[] = $this->eventPrefix.'FormElementPreUpdating';
    }

    protected function formElementPostUpdate()
    {
        $this->events[] = $this->eventPrefix.'FormElementPostUpdating';
    }

    protected function formElementPreDelete()
    {
        $this->events[] = $this->eventPrefix.'FormElementPreDeleting';
    }

    protected function formElementPostDelete()
    {
        $this->events[] = $this->eventPrefix.'FormElementPostDeleting';
    }
}
