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
use Mandango\Group\PolymorphicReferenceGroup;

class PolymorphicReferenceGroupTest extends TestCase
{
    public function testConstructor()
    {
        $group = new PolymorphicReferenceGroup('_mandango_document_class', $article = new \Model\Article(), 'related_ref');
        $this->assertSame($article, $group->getParent());
        $this->assertSame('related_ref', $group->getField());
        $this->assertFalse($group->getDiscriminatorMap());

        $discriminatorMap = array(
            'au' => 'Model\Author',
            'ct' => 'Model\Category',
        );
        $group = new PolymorphicReferenceGroup('_mandango_document_class', $article = new \Model\Article(), 'related_ref', $discriminatorMap);
        $this->assertSame($discriminatorMap, $group->getDiscriminatorMap());
    }
}
