<?php

/*
 * Copyright 2010 Pablo Díez <pablodip@gmail.com>
 *
 * This file is part of Mandango.
 *
 * Mandango is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Mandango is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Mandango. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mandango\Tests\Group;

use Mandango\Tests\TestCase;
use Mandango\Group\EmbeddedGroup;

class EmbeddedGroupTest extends TestCase
{
    public function testInitializeSaved()
    {
        $data = array(
            array('name' => 'foo'),
            array('name' => 'bar'),
        );

        $group = new EmbeddedGroup('Model\Comment');
        $group->setRootAndPath($article = new \Model\Article(), 'comments');
        $group->setSavedData($data);
        $this->assertSame(2, $group->count());
        $saved = $group->getSaved();
        $this->assertEquals(\Model\Comment::create()->setDocumentData($data[0]), $saved[0]);
        $this->assertSame(array('root' => $article, 'path' => 'comments.0'), $saved[0]->getRootAndPath());
        $this->assertEquals(\Model\Comment::create()->setDocumentData($data[0]), $saved[0]);
        $this->assertSame(array('root' => $article, 'path' => 'comments.1'), $saved[1]->getRootAndPath());
    }

    public function testRootAndPath()
    {
        $group = new EmbeddedGroup('Model\Comment');
        $comment = new \Model\Comment();
        $group->add($comment);
        $group->setRootAndPath($article = new \Model\Article(), 'comments');
        $this->assertSame(array('root' => $article, 'path' => 'comments._add0'), $comment->getRootAndPath());
    }

    public function testAdd()
    {
        $group = new EmbeddedGroup('Model\Comment');
        $group->setRootAndPath($article = new \Model\Article(), 'comments');
        $comment = new \Model\Comment();
        $group->add($comment);
        $this->assertSame(array('root' => $article, 'path' => 'comments._add0'), $comment->getRootAndPath());
    }

    public function testSavedData()
    {
        $group = new EmbeddedGroup('Model\Comment');
        $this->assertNull($group->getSavedData());
        $group->setSavedData($data = array(array('foo' => 'bar'), array('bar' => 'foo')));
        $this->assertSame($data, $group->getSavedData());
    }
}
