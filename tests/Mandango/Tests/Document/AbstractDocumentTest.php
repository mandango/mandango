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
    public function testCreate()
    {
        $this->assertEquals(new \Model\Article(), \Model\Article::create());
    }

    public function testDocumentData()
    {
        $document = new AbstractDocument();
        $this->assertSame(array(), $document->getDocumentData());
        $data = array('fields' => array('foo' => 'bar'));
        $document->setDocumentData($data);
        $this->assertSame($data, $document->getDocumentData());
    }
}
