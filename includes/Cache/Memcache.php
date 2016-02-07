<?php

namespace Arctic\Cache;

class Memcache implements Cache
{
    const FLAG = 0x10000;

    private $_prefix = '';

    /**
     * @var \Memcache
     */
    private $_mc;

    public function initiate( array $config=null ) {
        if (!class_exists('Memcache')) return false;

        // default configuration
        $default_config = array(
            'host'      =>  '127.0.0.1',
            'port'      =>  11211,
            'compress'  =>  8192,
            'persistent'=>  true,
            'prefix'    =>  null,
            'timeout'   =>  null
        );

        // merge default configuration
        if ( $config ) $config = array_merge($default_config, $config);
        else $config = $default_config;

        // prefix
        if (isset($config['prefix'])) {
            $this->_prefix = $config['prefix'] . '::';
        }

        // make memory connection
        $this->_mc = new \Memcache();

        // open connection (persistent or regular)
        if ($config['persistent']) $ret = @$this->_mc->pconnect($config['host'], $config['port'], $config['timeout']);
        else $ret = @$this->_mc->connect($config['host'], $config['port'], $config['timeout']);

        // unable to connect
        if (!$ret) return false;

        // set compression threshold
        if (isset($config['compress'])) {
            $this->_mc->setCompressThreshold($config['compress']);
        }

        // register shut down
        register_shutdown_function(array($this, 'disconnect'));

        return true;
    }

    public function disconnect() {
        // close is actually ignored if persistent connection
        if (isset($this->_mc)) $this->_mc->close();
    }

    public function insert( $key , $value , $ttl=0 ) {
        return $this->_mc->add($this->_prefix . $key, $value, self::FLAG, $ttl);
    }

    public function update( $key , $value , $ttl=0 ) {
        return $this->_mc->replace($this->_prefix . $key, $value, self::FLAG, $ttl);
    }

    public function set( $key , $value , $ttl=0 ) {
        $this->_mc->set($this->_prefix . $key, $value, self::FLAG, $ttl);
    }

    public function get( $key ) {
        $flags = 0;
        $ret = $this->_mc->get($this->_prefix . $key, $flags);
        if ($flags > 0) return $ret;
        return null;
    }

    public function remove( $key ) {
        $this->_mc->delete($this->_prefix . $key);
    }

    public static function isViableDefaultCacheType(array $config=null) {
        return false;
    }
}
