<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Tests\Logger;

use Mandango\Logger\Time;

class TimeTest extends \PHPUnit_Framework_TestCase
{
    public function testTime()
    {
        $time = new Time();
        $time->start();

        $this->assertTrue(is_int($time->stop()));
    }
}
