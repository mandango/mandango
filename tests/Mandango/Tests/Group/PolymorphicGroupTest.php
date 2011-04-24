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
use Mandango\Group\PolymorphicGroup as BasePolymorphicGroup;

class PolymorphicGroup extends BasePolymorphicGroup
{
    public $forSaved = array();

    protected function doInitializeSavedData()
    {
        return $this->forSaved;
    }
}

class PolymorphicGroupTest extends TestCase
{
    public function testConstructor()
    {
        $group = new PolymorphicGroup('my_discriminatorField');
        $this->assertSame('my_discriminatorField', $group->getDiscriminatorField());
    }
}
