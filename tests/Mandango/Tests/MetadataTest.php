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
