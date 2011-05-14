<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Tests\Group;

use Mandango\Tests\TestCase;
use Mandango\Group\PolymorphicReferenceGroup;

class PolymorphicReferenceGroupTest extends TestCase
{
    public function testConstructor()
    {
        $group = new PolymorphicReferenceGroup('_mandangoDocumentClass', $article = $this->mandango->create('Model\Article'), 'related_ref');
        $this->assertSame($article, $group->getParent());
        $this->assertSame('related_ref', $group->getField());
        $this->assertFalse($group->getDiscriminatorMap());

        $discriminatorMap = array(
            'au' => 'Model\Author',
            'ct' => 'Model\Category',
        );
        $group = new PolymorphicReferenceGroup('_mandangoDocumentClass', $article = $this->mandango->create('Model\Article'), 'related_ref', $discriminatorMap);
        $this->assertSame($discriminatorMap, $group->getDiscriminatorMap());
    }
}
