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

namespace Mandango\Cache;

/**
 * FilesystemCache.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class FilesystemCache implements CacheInterface
{
    protected $dir;

    /**
     * Constructor.
     *
     * @param string $dir The directory.
     */
    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return file_exists($this->dir.'/'.$key.'.php');
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $file = $this->dir.'/'.$key.'.php';

        return file_exists($file) ? require($file) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        if (!is_dir($this->dir) && false === @mkdir($this->dir, 0777, true)) {
            throw new \RuntimeException(sprintf('Unable to create the "%s" directory.', $this->dir));
        }

        $file = $this->dir.'/'.$key.'.php';
        $valueExport = var_export($value, true);
        $content = <<<EOF
<?php

return $valueExport;
EOF;

        if (false === @file_put_contents($file, $content)) {
            throw new \RuntimeException(sprintf('Unable to write the "%s" file.', $file));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        $file = $this->dir.'/'.$key.'.php';
        if (file_exists($file) && false === @unlink($file)) {
            throw new \RuntimeException(sprintf('Unable to remove the "%s" file.', $file));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        if (is_dir($this->dir)) {
            foreach (new \DirectoryIterator($this->dir) as $file) {
                if ($file->isFile()) {
                    if (false === @unlink($file->getRealPath())) {
                        throw new \RuntimeException(sprintf('Unable to remove the "%s" file.', $file->getRealPath()));
                    }
                }
            }
        }
    }
}
