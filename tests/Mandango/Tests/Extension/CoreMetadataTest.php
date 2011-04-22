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
        $this->assertTrue(isset($metadata['referencesOne']['author']));
        $this->assertTrue(isset($metadata['referencesOne']['author']['inherited']));
        $this->assertTrue($metadata['referencesOne']['author']['inherited']);
        $this->assertTrue(isset($metadata['referencesOne']['authorLocal']));
        $this->assertTrue(isset($metadata['referencesOne']['authorLocal']['inherited']));
        $this->assertFalse($metadata['referencesOne']['authorLocal']['inherited']);

        // referencesMany
        $this->assertTrue(isset($metadata['referencesMany']['categories']));
        $this->assertTrue(isset($metadata['referencesMany']['categories']));
        $this->assertTrue($metadata['referencesMany']['categories']['inherited']);
        $this->assertTrue(isset($metadata['referencesMany']['categoriesLocal']));
        $this->assertTrue(isset($metadata['referencesMany']['categoriesLocal']['inherited']));
        $this->assertFalse($metadata['referencesMany']['categoriesLocal']['inherited']);

        // embeddedsOne
        $this->assertTrue(isset($metadata['embeddedsOne']['source']));
        $this->assertTrue(isset($metadata['embeddedsOne']['source']['inherited']));
        $this->assertTrue($metadata['embeddedsOne']['source']['inherited']);
        $this->assertTrue(isset($metadata['embeddedsOne']['sourceLocal']));
        $this->assertTrue(isset($metadata['embeddedsOne']['sourceLocal']['inherited']));
        $this->assertFalse($metadata['embeddedsOne']['sourceLocal']['inherited']);

        // embeddedsMany
        $this->assertTrue(isset($metadata['embeddedsMany']['comments']));
        $this->assertTrue(isset($metadata['embeddedsMany']['comments']['inherited']));
        $this->assertTrue($metadata['embeddedsMany']['comments']['inherited']);
        $this->assertTrue(isset($metadata['embeddedsMany']['commentsLocal']));
        $this->assertTrue(isset($metadata['embeddedsMany']['commentsLocal']['inherited']));
        $this->assertFalse($metadata['embeddedsMany']['commentsLocal']['inherited']);
    }
}
