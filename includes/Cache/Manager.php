<?php

namespace Arctic\Cache;

use Arctic\Exception;

class Manager
{
    const DEFAULT_CACHE = 60;

    /**
     * @var Cache
     */
    private $_cache;

    /**
     * The cache profile can be a global value (bool or closure or integer for seconds). It can also be an array of
     * values with keys representing classes and/or namespaces and returning a value.
     * @var int|bool|array
     */
    private $_cache_profile;

    private $_next_force;

    public function __construct($cache_type=null, array $cache_config=null) {
        // allow condensed format [type=>"",...]
        if ( $cache_config === null && is_array( $cache_type ) ) {
            if (!isset($cache_config['type'])) throw new Exception('If a combined cache configuration is used, then it must have the key "type".');
            $cache_type = $cache_config['type'];
        }

        // detect caching?
        if ($cache_type === null) {
            if (function_exists('apc_add')) $cache_type = __NAMESPACE__ . '\Apc';
            elseif (function_exists('xcache_get')) $cache_type = __NAMESPACE__ . '\XCache';
            else $cache_type = __NAMESPACE__ . '\Local';
        }

        // no cache
        if ($cache_type === false) return;

        // is string?
        if (is_string($cache_type)) {
            // use local cache name
            if (strpos($cache_type,'\\') === false) $cache_type = __NAMESPACE__ . '\\' . $cache_type;

            // initiate cache
            $cache_type = new $cache_type();
        }

        // valid
        if (!($cache_type instanceof Cache)) {
            throw new Exception('Invalid Cache object passed.');
        }

        // initiate
        if ($cache_type->initiate($cache_config)) {
            $this->_cache = $cache_type;
        }
    }

    public function forceCacheForNext() {
        $this->_next_force = true;
    }

    public function forceNoCacheForNext() {
        $this->_next_force = false;
    }

    public function setCacheProfile($cache_profile) {
        $this->_cache_profile = $cache_profile;
    }

    private function _getCacheProfile($key, $class=null) {
        // get cache profile
        if (is_array($this->_cache_profile)) {
            // make array of keys to check
            $profile_keys = [];
            if ($class) {
                $segments = explode('\\',$class);;
                while ($segments) {
                    $profile_keys[] = implode('\\',$segments);
                    array_pop($segments);
                }
            }
            $profile_keys[] = '';

            // look for cache profile
            $profile = null;
            foreach ($profile_keys as $profile_key) {
                if (isset($this->_cache_profile[$profile_key])) {
                    // get profile definition
                    $profile = $this->_cache_profile[$profile_key];
                    break;
                }
            }
        }
        else {
            $profile = $this->_cache_profile;
        }

        // no profile? use cache profile entry
        if (!isset($profile) && $class) {
            if (method_exists($class, 'getCacheProfile')) {
                return call_user_func([$class, 'getCacheProfile'], $key);
            }
        }

        // no profile?
        if (!isset($profile)) {
            return null;
        }

        // is callable
        if (is_bool($profile) || is_int($profile)) {
            return $profile;
        }

        // call
        if (is_callable($profile)) {
            return call_user_func($profile, $key, $class);
        }

        return null;
    }

    /**
     * Gets a value from the cache if the profile is not false.
     * @param string $key
     * @param string|null $class The class with which the key is associated with. This gets appended as the key, but also is used for determining caching profiles.
     * @param int|bool|null $default_profile The default profile to use if neither class nor the cache manager profile specifies a caching profile.
     * @return mixed
     */
    public function get($key, $class=null, $default_profile=null) {
        // no cache
        if (!$this->_cache) {
            return null;
        }

        // next
        if (isset($this->_next_force)) {
            // force next value?
            $force = $this->_next_force;
            $this->_next_force = null;

            if ( !$force ) return null;
        }
        else {
            // get profile
            $profile = $this->_getCacheProfile($key, $class);
            if ( $profile === false ) return null;
        }

        // run cache
        if ($class) $key = sprintf('%s::%s', $class, $key);
        return $this->_cache->get($key);
    }

    /**
     * @param string $key
     * @param mixed $value The value to set.
     * @param string|null $class The class with which the key is associated with. This gets appended as the key, but also is used for determining caching profiles.
     * @param int|bool|null $default_profile The default profile to use if neither class nor the cache manager profile specifies a caching profile.
     */
    public function set($key, $value, $class=null, $default_profile=null) {
        // no cache
        if (!$this->_cache) {
            return;
        }

        // get profile
        $profile = $this->_getCacheProfile($key, $class);
        if ( $profile === false ) return;

        // use default cache profile
        if ( $profile === null ) $profile = ($default_profile ? $default_profile : self::DEFAULT_CACHE);

        // run cache
        if ($class) $key = sprintf('%s::%s', $class, $key);
        $this->_cache->set($key, $value, ($profile === true ? 0 : $profile));
    }

    /**
     * @param $key
     * @param string|null $class The class with which the key is associated with. This gets appended as the key, but also is used for determining caching profiles.
     */
    public function remove($key, $class=null) {
        // no cache
        if (!$this->_cache) {
            return;
        }

        // run cache
        if ($class) $key = sprintf('%s::%s', $class, $key);
        $this->_cache->remove($key);
    }
}
