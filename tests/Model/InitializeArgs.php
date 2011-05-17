<?php

namespace Model;

/**
 * Model\InitializeArgs document.
 */
class InitializeArgs extends \Model\Base\InitializeArgs
{
    public function initialize(Author $author)
    {
        $this->setAuthor($author);
    }
}
