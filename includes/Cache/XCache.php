<?php

namespace Arctic\Cache;

class XCache implements Cache
{
    private $_prefix = '';

    public function initiate( array $config=null ) {
        if ($config && isset($config['prefix'])) {
            $this->_prefix = $config['prefix'] . '::';
        }

        return function_exists('xcache_get');
    }

    public function insert( $key , $value , $ttl=0 ) {
        if (!xcache_isset($this->_prefix . $key)) {
            xcache_set($this->_prefix . $key, $value, $ttl);
        }
    }

    public function update( $key , $value , $ttl=0 ) {
        if (xcache_isset($this->_prefix . $key)) {
            xcache_set($this->_prefix . $key, $value, $ttl);
        }
    }

    public function set( $key , $value , $ttl=0 ) {
        xcache_set($this->_prefix . $key, $value, $ttl);
    }

    public function get( $key ) {
        return xcache_get($this->_prefix . $key);
    }

    public function remove( $key ) {
        xcache_unset($this->_prefix . $key);
    }

    public static function isViableDefaultCacheType(array $config=null) {
        return function_exists('xcache_get');
    }
}
