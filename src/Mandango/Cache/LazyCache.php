<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Cache;

/**
 * @author Pablo Díez <pablodip@gmail.com>
 */
class LazyCache implements CacheInterface
{
    private $delegate;
    private $data = array();

    public function __construct(CacheInterface $delegate)
    {
        $this->delegate = $delegate;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        $this->initKey($key);

        return isset($this->data[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $this->initKey($key);

        return $this->data[$key];
    }

    private function initKey($key)
    {
        if (!array_key_exists($key, $this->data)) {
            $this->data[$key] = $this->delegate->get($key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
        $this->delegate->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        unset($this->data[$key]);
        $this->delegate->remove($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->data = array();
        $this->delegate->clear();
    }
}
