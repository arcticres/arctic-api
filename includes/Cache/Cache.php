<?php

namespace Arctic\Cache;

use Arctic\Api;

interface Cache
{
    /**
     * Initiate the cache.
     * @param array|null $config
     * @return bool
     */
    public function initiate( array $config=null );

    /**
     * Create new entry in the cache only if the key does not exist in the cache.
     * @param string $key
     * @param mixed $value
     * @param int $ttl 0 for no timeout, otherwise time in seconds.
     * @return bool
     */
    public function insert( $key , $value , $ttl=0 );

    /**
     * Update an existing entry in the cache, but only if it is exists.
     * @param string $key
     * @param mixed $value
     * @param int $ttl 0 for no timeout, otherwise time in seconds.
     * @return bool
     */
    public function update( $key , $value , $ttl=0 );

    /**
     * Insert or update an entry in the cache.
     * @param string $key
     * @param mixed $value
     * @param int $ttl 0 for no timeout, otherwise time in seconds.
     */
    public function set( $key , $value , $ttl=0 );

    /**
     * Fetch a value from the cache.
     * @param string $key
     * @return mixed
     */
    public function get( $key );

    /**
     * Remove a key from the cache.
     * @param string $key
     */
    public function remove( $key );

    /**
     * Whether or not this cache is a viable default type.
     * @param array|null $config
     * @return bool
     */
    public static function isViableDefaultCacheType(array $config=null);
}
