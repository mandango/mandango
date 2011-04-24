<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Logger;

/**
 * Time.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class Time
{
    private $time;

    /**
     * Start to count the time.
     */
    public function start()
    {
        $this->time = microtime(true);
    }

    /**
     * Stop of count the time and returns the result.
     *
     * @return int The result.
     */
    public function stop()
    {
        $time = (int) round((microtime(true) - $this->time) * 1000);

        $this->time = null;

        return $time;
    }
}
