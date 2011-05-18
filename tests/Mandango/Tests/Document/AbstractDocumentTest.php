<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Tests\Document;

use Mandango\Tests\TestCase;
use Mandango\Document\AbstractDocument as BaseAbstractDocument;

class AbstractDocument extends BaseAbstractDocument
{
    public function setDocumentData($data)
    {
        $this->data = $data;
    }
}

class AbstractDocumentTest extends TestCase
{
    public function testGetMandango()
    {
        $document = new AbstractDocument($this->mandango);
        $this->assertSame($this->mandango, $document->getMandango());
    }

    public function testCreate()
    {
        $this->assertEquals($this->mandango->create('Model\Article'), $this->mandango->create('Model\Article'));
    }

    public function testDocumentData()
    {
        $document = new AbstractDocument($this->mandango);
        $this->assertSame(array(), $document->getDocumentData());
        $data = array('fields' => array('foo' => 'bar'));
        $document->setDocumentData($data);
        $this->assertSame($data, $document->getDocumentData());
    }
}
