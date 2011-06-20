<?php

namespace Model;

/**
 * Model\TextTextElement document.
 */
class TextTextElement extends \Model\Base\TextTextElement
{
    private $events = array();

    public function getEvents()
    {
        return $this->events;
    }

    protected function textTextElementPreInsert()
    {
        $this->events[] = 'TextTextElementPreInsert';
    }
}
