<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Tests;

use Mandango\MetadataFactory as BaseMetadataFactory;

class MetadataFactory extends BaseMetadataFactory
{
    protected $classes = array(
        'Model\Article'  => false,
        'Model\Author'   => false,
        'Model\Comment'  => true,
        'Model\Category' => false,
        'Model\Source'   => true,
    );
}

class MetadataFactoryInfo
{
    public function getModelArticleClass()
    {
        return 'foo';
    }

    public function getModelAuthorClass()
    {
        return 'bar';
    }

    public function getModelCommentClass()
    {
        return 'ups';
    }
}

class MetadataFactoryTest extends TestCase
{
    public function testGetClasses()
    {
        $metadata = new MetadataFactory();
        $this->assertSame(array(
            'Model\Article',
            'Model\Author',
            'Model\Comment',
            'Model\Category',
            'Model\Source',
        ), $metadata->getClasses());
    }

    public function testGetDocumentClasses()
    {
        $metadata = new MetadataFactory();
        $this->assertSame(array(
            'Model\Article',
            'Model\Author',
            'Model\Category',
        ), $metadata->getDocumentClasses());
    }

    public function testGetEmbeddedDocumentClasses()
    {
        $metadata = new MetadataFactory();
        $this->assertSame(array(
            'Model\Comment',
            'Model\Source',
        ), $metadata->getEmbeddedDocumentClasses());
    }

    public function testHasClass()
    {
        $metadata = new MetadataFactory();
        $this->assertTrue($metadata->hasClass('Model\Article'));
        $this->assertTrue($metadata->hasClass('Model\Comment'));
        $this->assertFalse($metadata->hasClass('Model\User'));
    }

    public function testIsDocumentClass()
    {
        $metadata = new MetadataFactory();
        $this->assertTrue($metadata->isDocumentClass('Model\Article'));
        $this->assertTrue($metadata->isDocumentClass('Model\Author'));
        $this->assertFalse($metadata->isDocumentClass('Model\Comment'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testIsDocumentClassClassDoesNotExist()
    {
        $metadata = new MetadataFactory();
        $metadata->isDocumentClass('Model\User');
    }

    public function testIsEmbeddedDocumentClass()
    {
        $metadata = new MetadataFactory();
        $this->assertTrue($metadata->isEmbeddedDocumentClass('Model\Comment'));
        $this->assertTrue($metadata->isEmbeddedDocumentClass('Model\Source'));
        $this->assertFalse($metadata->isEmbeddedDocumentClass('Model\Article'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testIsEmbeddedDocumentClassClassDoesNotExist()
    {
        $metadata = new MetadataFactory();
        $metadata->isEmbeddedDocumentClass('Model\User');
    }

    public function testGetClass()
    {
        $metadata = new MetadataFactory();
        $this->assertSame('foo', $metadata->getClass('Model\Article'));
        $this->assertSame('bar', $metadata->getClass('Model\Author'));
        $this->assertSame('ups', $metadata->getClass('Model\Comment'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetClassInfoClassDoesNotExist()
    {
        $metadata = new MetadataFactory();
        $metadata->getClass('Model\User');
    }
}
