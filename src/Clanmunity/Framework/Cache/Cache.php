<?php

namespace Clanmunity\Framework\Cache;

class Cache
{
    protected $cacheKeyCollection = array();

    public function add($cacheKey, $content)
    {
        if (!empty($cacheKey)) {
            $this->cacheKeyCollection[$cacheKey] = $this->serialize($content);
        }
    }

    public function remove($cacheKey)
    {
        if ($this->has($cacheKey)) {
            unset($this->cacheKeyCollection[$cacheKey]);
        }
    }

    public function has($cacheKey)
    {
        return isset($this->cacheKeyCollection[$cacheKey]);
    }

    public function serialize($content)
    {

    }
} 
