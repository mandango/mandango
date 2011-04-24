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

use Mandango\Metadata as BaseMetadata;

class Metadata extends BaseMetadata
{
    protected $classes = array(
        'Model\Article'  => false,
        'Model\Author'   => false,
        'Model\Comment'  => true,
        'Model\Category' => false,
        'Model\Source'   => true,
    );
}

class MetadataInfo
{
    public function getModelArticleClassInfo()
    {
        return 'foo';
    }

    public function getModelAuthorClassInfo()
    {
        return 'bar';
    }

    public function getModelCommentClassInfo()
    {
        return 'ups';
    }
}

class MetadataTest extends TestCase
{
    public function testGetClasses()
    {
        $metadata = new Metadata();
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
        $metadata = new Metadata();
        $this->assertSame(array(
            'Model\Article',
            'Model\Author',
            'Model\Category',
        ), $metadata->getDocumentClasses());
    }

    public function testGetEmbeddedDocumentClasses()
    {
        $metadata = new Metadata();
        $this->assertSame(array(
            'Model\Comment',
            'Model\Source',
        ), $metadata->getEmbeddedDocumentClasses());
    }

    public function testHasClass()
    {
        $metadata = new Metadata();
        $this->assertTrue($metadata->hasClass('Model\Article'));
        $this->assertTrue($metadata->hasClass('Model\Comment'));
        $this->assertFalse($metadata->hasClass('Model\User'));
    }

    public function testIsDocumentClass()
    {
        $metadata = new Metadata();
        $this->assertTrue($metadata->isDocumentClass('Model\Article'));
        $this->assertTrue($metadata->isDocumentClass('Model\Author'));
        $this->assertFalse($metadata->isDocumentClass('Model\Comment'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testIsDocumentClassClassDoesNotExist()
    {
        $metadata = new Metadata();
        $metadata->isDocumentClass('Model\User');
    }

    public function testIsEmbeddedDocumentClass()
    {
        $metadata = new Metadata();
        $this->assertTrue($metadata->isEmbeddedDocumentClass('Model\Comment'));
        $this->assertTrue($metadata->isEmbeddedDocumentClass('Model\Source'));
        $this->assertFalse($metadata->isEmbeddedDocumentClass('Model\Article'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testIsEmbeddedDocumentClassClassDoesNotExist()
    {
        $metadata = new Metadata();
        $metadata->isEmbeddedDocumentClass('Model\User');
    }

    public function testGetClassInfo()
    {
        $metadata = new Metadata();
        $this->assertSame('foo', $metadata->getClassInfo('Model\Article'));
        $this->assertSame('bar', $metadata->getClassInfo('Model\Author'));
        $this->assertSame('ups', $metadata->getClassInfo('Model\Comment'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetClassInfoClassDoesNotExist()
    {
        $metadata = new Metadata();
        $metadata->getClassInfo('Model\User');
    }
}
