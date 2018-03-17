<?php

namespace Arctic\Cache;

class Memcached implements Cache
{
	const FLAG = 0x10000;

	protected $_key_max_len = 250;

	/**
	 * @var \Memcached
	 */
	private $_mc;

	public function initiate( array $config=null ) {
		if (!class_exists('Memcached')) return false;

		// default configuration
		$default_config = array(
			'host'      =>  '127.0.0.1',
			'port'      =>  11211,
			'persistent'=>  true,
			'prefix'    =>  null
		);

		// merge default configuration
		if ( $config ) $config = array_merge($default_config, $config);
		else $config = $default_config;

		// make memory connection
		$this->_mc = new \Memcache();

		// prefix
		$this->_mc->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
		$this->_mc->setOption(\Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
		if (isset($config['prefix'])) {
			$this->_mc->setOption(\Memcached::OPT_PREFIX_KEY, $config['prefix']);
			$this->_key_max_len = 250 - strlen($config['prefix']);
		}


		// persistent connection
		$persistent = (bool)$config['persistent'];

		// configuration
		$host = $config['host'];
		$port = (int)$config['port'];


		if ($persistent) {
			$this->_mc = new \Memcached('mc');
		}
		else {
			$this->_mc = new \Memcached();
		}

		// add server
		if (0 === count($this->_mc->getServerList())) {
			$this->_mc->addServer($host, $port);
		}

		// register shut down
		register_shutdown_function(array($this, 'disconnect'));

		return true;
	}

	public function disconnect() {
		if (isset($this->_mc) && !$this->_mc->isPersistent()) {
			$this->_mc->quit();
		}
	}

	protected function _encodeKey($key) {
		if (strlen($key) > $this->_key_max_len) {
			return substr($key, 0, $this->_key_max_len - 40) . sha1($key);
		}
		return $key;
	}

	public function insert($key, $value, $ttl=0) {
		// encode key
		$key = $this->_encodeKey($key);

		// add and check for true (success)
		if ($this->_mc->add($key, $value, $ttl)) {
			return true;
		}

		// only fail on insert related errors
		switch ($this->_mc->getResultCode()) {
			case \Memcached::RES_NOTSTORED:
			case \Memcached::RES_DATA_EXISTS:
				return false;
		}

		return true;
	}

	public function update($key, $value, $ttl=0) {
		// encode key
		$key = $this->_encodeKey($key);

		// add and check for true (success)
		if ($this->_mc->replace($key, $value, $ttl)) {
			return true;
		}

		// only fail on replace related errors
		switch ($this->_mc->getResultCode()) {
			case \Memcached::RES_NOTSTORED:
				return false;
		}

		return true;
	}

	public function set($key, $value, $ttl=0) {
		// encode key
		$key = $this->_encodeKey($key);

		$this->_mc->set($key, $value, $ttl);
	}

	public function get($key) {
		// encode key
		$key = $this->_encodeKey($key);

		// fetch
		$val = $this->_mc->get($key);

		// false can be a value in the cache, or indicate a problem with the lookup (including key not found)
		if (false === $val && \Memcached::RES_SUCCESS !== $this->_mc->getResultCode()) return null;

		return $val;
	}

	public function remove($key) {
		// encode key
		$key = $this->_encodeKey($key);

		// delete one
		$this->_mc->delete($key);
	}

	public static function isViableDefaultCacheType(array $config=null) {
		return false;
	}
}
