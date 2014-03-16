<?php

namespace Arctic;

class ModelSet implements \IteratorAggregate, \ArrayAccess, \Countable
{
	protected $_paginated = false;
	protected $_start;
	protected $_number;
	protected $_page;
	protected $_total;

	/**
	 * @var Model[]
	 */
	protected $_data;

	public function __construct( $model_class , $response ) {
		if ( isset( $response[ 'start' ] ) ) {
			$this->_paginated = true;
			$this->_start = $response[ 'start' ];
			$this->_number = $response[ 'number' ];
			$this->_page = $response[ 'page' ];
			$this->_total = $response[ 'total' ];
		}
		else {
			$this->_total = count( $response[ 'entries' ] );
		}

		$this->_data = array();
		foreach ( $response[ 'entries' ] as $arr ) {
			/** @var Model $obj */
			$obj = new $model_class();
			$obj->fillExistingData( isset( $arr[ 'id' ] ) ? $arr[ 'id' ] : null , $arr );
			$this->_data[] = $obj;
		}
	}

	public function getIterator() {
		return new \ArrayIterator($this->_data);
	}

	public function offsetExists($offset) {
		return isset( $this->_data[ $offset ] );
	}

	public function offsetGet($offset) {
		if ( isset( $this->_data[ $offset ] ) ) {
			return $this->_data[ $offset ];
		}
		return null;
	}

	public function offsetSet($offset, $value) {
		Api::getInstance()->raiseError('Invalid Access','You can not write to model sets.');
	}

	public function offsetUnset($offset) {
		Api::getInstance()->raiseError('Invalid Access','You can not write to model sets.');
	}

	/**
	 * @return boolean
	 */
	public function isPaginated() {
		return $this->_paginated;
	}

	/**
	 * @return int
	 */
	public function getStart() {
		if ( !$this->_paginated ) return 0;
		return $this->_start;
	}

	/**
	 * @return int
	 */
	public function getNumber() {
		if ( !$this->_paginated ) return $this->getTotal();
		return $this->_number;
	}

	/**
	 * @return int
	 */
	public function getPage() {
		if ( !$this->_paginated ) return 0;
		return $this->_page;
	}

	/**
	 * @return int
	 */
	public function getTotal() {
		return $this->_total;
	}

	public function count() {
		return count( $this->_data );
	}

	public function getPreviousArguments() {
		if ( $this->_paginated && $this->_start > 0 ) {
			return array( max( $this->_start - $this->_number , 0 ) , $this->_number );
		}
		return false;
	}

	public function getNextArguments() {
		if ( $this->_paginated && ( $this->_start + $this->_number ) < $this->_total ) {
			return array( $this->_start + $this->_number , $this->_number );
		}
		return false;
	}
}
