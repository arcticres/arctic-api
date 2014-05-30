<?php

namespace Arctic\Cache;

class Apc implements Cache
{
    private $_prefix = '';

    public function initiate( array $config=null ) {
        if ($config && isset($config['prefix'])) {
            $this->_prefix = $config['prefix'] . '::';
        }

        return function_exists('apc_add');
    }

    public function insert( $key , $value , $ttl=0 ) {
        apc_add($this->_prefix . $key, $value, $ttl);
    }

    public function update( $key , $value , $ttl=0 ) {
        if (apc_exists($this->_prefix . $key)) {
            apc_store($this->_prefix . $key, $value, $ttl);
        }
    }

    public function set( $key , $value , $ttl=0 ) {
        apc_store($this->_prefix . $key, $value, $ttl);
    }

    public function get( $key ) {
        $ret = apc_fetch($this->_prefix . $key, $success);
        if ( $success ) return $ret;
        return null;
    }

    public function remove( $key ) {
        apc_delete($this->_prefix . $key);
    }

    public static function isViableDefaultCacheType(array $config=null) {
        return function_exists('apc_add');
    }
}
