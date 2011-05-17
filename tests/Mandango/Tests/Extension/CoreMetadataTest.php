<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Tests\Extension;

use Mandango\Tests\TestCase;

class CoreMetadataTest extends TestCase
{
    public function testMetadata()
    {
        $this->assertSame($this->metadataFactory->getClass('Model\Article'), $this->mandango->getRepository('Model\Article')->getMetadata());
        $this->assertSame($this->metadataFactory->getClass('Model\Source'), $this->mandango->getMetadataFactory()->getClass('Model\Source'));
    }

    public function testInherited()
    {
        $metadata = $this->metadataFactory->getClass('Model\RadioFormElement');

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
