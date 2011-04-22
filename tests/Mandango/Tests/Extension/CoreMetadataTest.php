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

namespace Mandango\Tests\Extension;

use Mandango\Tests\TestCase;

class CoreMetadataTest extends TestCase
{
    public function testMetadata()
    {
        $this->assertSame($this->metadata->getClassInfo('Model\Article'), \Model\Article::getMetadata());
        $this->assertSame($this->metadata->getClassInfo('Model\Source'), \Model\Source::getMetadata());
    }

    public function testInherited()
    {
        $metadata = $this->metadata->getClassInfo('Model\RadioFormElement');

        // fields
        $this->assertTrue(isset($metadata['fields']['label']));
        $this->assertTrue(isset($metadata['fields']['label']['inherited']));
        $this->assertTrue($metadata['fields']['label']['inherited']);
        $this->assertTrue(isset($metadata['fields']['options']));
        $this->assertTrue(isset($metadata['fields']['options']['inherited']));
        $this->assertFalse($metadata['fields']['options']['inherited']);

        // referencesOne
        $this->assertTrue(isset($metadata['references_one']['author']));
        $this->assertTrue(isset($metadata['references_one']['author']['inherited']));
        $this->assertTrue($metadata['references_one']['author']['inherited']);
        $this->assertTrue(isset($metadata['references_one']['authorLocal']));
        $this->assertTrue(isset($metadata['references_one']['authorLocal']['inherited']));
        $this->assertFalse($metadata['references_one']['authorLocal']['inherited']);

        // referencesMany
        $this->assertTrue(isset($metadata['references_many']['categories']));
        $this->assertTrue(isset($metadata['references_many']['categories']));
        $this->assertTrue($metadata['references_many']['categories']['inherited']);
        $this->assertTrue(isset($metadata['references_many']['categoriesLocal']));
        $this->assertTrue(isset($metadata['references_many']['categoriesLocal']['inherited']));
        $this->assertFalse($metadata['references_many']['categoriesLocal']['inherited']);

        // embeddedsOne
        $this->assertTrue(isset($metadata['embeddeds_one']['source']));
        $this->assertTrue(isset($metadata['embeddeds_one']['source']['inherited']));
        $this->assertTrue($metadata['embeddeds_one']['source']['inherited']);
        $this->assertTrue(isset($metadata['embeddeds_one']['sourceLocal']));
        $this->assertTrue(isset($metadata['embeddeds_one']['sourceLocal']['inherited']));
        $this->assertFalse($metadata['embeddeds_one']['sourceLocal']['inherited']);

        // embeddedsMany
        $this->assertTrue(isset($metadata['embeddeds_many']['comments']));
        $this->assertTrue(isset($metadata['embeddeds_many']['comments']['inherited']));
        $this->assertTrue($metadata['embeddeds_many']['comments']['inherited']);
        $this->assertTrue(isset($metadata['embeddeds_many']['commentsLocal']));
        $this->assertTrue(isset($metadata['embeddeds_many']['commentsLocal']['inherited']));
        $this->assertFalse($metadata['embeddeds_many']['commentsLocal']['inherited']);
    }
}
