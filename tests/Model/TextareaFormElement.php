<?php

namespace Model;

/**
 * Model\TextareaFormElement document.
 */
class TextareaFormElement extends \Model\Base\TextareaFormElement
{
    protected function textareaPreInsert()
    {
        $this->events[] = $this->eventPrefix.'TextareaPreInserting';
    }

    protected function textareaPostInsert()
    {
        $this->events[] = $this->eventPrefix.'TextareaPostInserting';
    }

    protected function textareaPreUpdate()
    {
        $this->events[] = $this->eventPrefix.'TextareaPreUpdating';
    }

    protected function textareaPostUpdate()
    {
        $this->events[] = $this->eventPrefix.'TextareaPostUpdating';
    }

    protected function textareaPreDelete()
    {
        $this->events[] = $this->eventPrefix.'TextareaPreDeleting';
    }

    protected function textareaPostDelete()
    {
        $this->events[] = $this->eventPrefix.'TextareaPostDeleting';
    }
}
