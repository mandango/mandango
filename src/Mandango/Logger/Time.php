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

namespace Mandango\Logger;

/**
 * Time.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class Time
{
    protected $time;

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
