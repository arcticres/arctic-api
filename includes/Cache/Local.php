<?php

namespace Arctic\Cache;

class Local implements Cache
{
    private $_data;

    public function initiate( array $config=null ) {
        return true;
    }

    public function insert( $key , $value , $ttl=0 ) {
        if (!isset($this->_data[$key]) ) {
            $this->_data[$key] = $value;
        }
    }

    public function update( $key , $value , $ttl=0 ) {
        if (isset($this->_data[$key]) ) {
            $this->_data[$key] = $value;
        }
    }

    public function set( $key , $value , $ttl=0 ) {
        $this->_data[$key] = $value;
    }

    public function get( $key ) {
        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        }
        return null;
    }

    public function remove( $key ) {
        unset($this->_data[$key]);
    }

    public static function isViableDefaultCacheType(array $config=null) {
        return true;
    }
}
