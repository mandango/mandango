<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Tests\Group;

use Mandango\Tests\TestCase;
use Mandango\Group\Group as BaseGroup;

class Group extends BaseGroup
{
    public $forSaved = array();

    protected function doInitializeSavedData()
    {
        return $this->forSaved;
    }
}

class GroupTest extends TestCase
{
    public function testConstructor()
    {
        $group = new Group('Model\Comment');
        $this->assertSame('Model\Comment', $group->getDocumentClass());
    }
}
