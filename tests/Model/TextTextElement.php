<?php

namespace Model;

/**
 * Model\TextTextElement document.
 */
class TextTextElement extends \Model\Base\TextTextElement
{
    protected function textTextElementPreInsert()
    {
        $this->events[] = 'TextTextElementPreInsert';
    }
}
