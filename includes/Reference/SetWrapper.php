<?php

namespace Arctic\Reference;

use Arctic\Api;
use Arctic\Model;
use Arctic\ModelSet;

class SetWrapper extends ModelSet
{
	protected $_loaded = false;

	/**
	 * @var Model
	 */
	protected $_parent;

	/**
	 * @var string
	 */
	protected $_model_class;

	/**
	 * @var Definition
	 */
	protected $_definition;

	public function __construct($parent, Definition $definition, array $data=null) {
		parent::__construct( $definition->getModelClass() , array( 'entries' => array() ) );

		// potential memory leak issue
		$this->_parent = $parent;

		// use sub api path
		$this->_definition = $definition;
		$this->_model_class = $this->_definition->getModelClass();

		// set object
		if ($data) {
			$this->_setLoadedData($data);
		}
	}

	/**
	 * @param Model[] $entries
	 */
	protected function _setLoadedData( $entries ) {
		$this->_loaded = true;
		$this->_data = array();

		foreach ( $entries as $entry ) {
			// still an array? turn it into an object
			if ( !is_object( $entry ) ) {
				$class = $this->_model_class;

				/** @var Model $obj */
				$obj = new $class();
				$obj->fillExistingData( isset( $entry[ 'id' ] ) ? $entry[ 'id' ] : null , $entry );
				$entry = $obj;
			}

			$entry->setParentReference( $this->_parent , $this->_definition );
			$this->_data[] = $entry;
		}
	}

	/**
	 * Gets the data without loading. Used by some methods to access references for uninserted objects.
	 * @internal
	 * @return Model[]
	 */
	public function getRawData() {
		if (isset($this->_data)) {
			return $this->_data;
		}
		return array();
	}

	protected function _load() {
		if ( $sub_api_path = $this->_definition->getSubApiPath() ) {
			// does not exist?
			if (!$this->_parent->doesExist()) {
				// do not actually flag as loaded, but return true and initialize
				// important for new references
				if (!isset($this->_data)) $this->_data = array();
				return true;
			}

			// prefix path
            Model::forceRelativeApiPath( $this->_parent->getMyRelativeApiPath() . '/' . $sub_api_path );

			// run query
			$class = $this->_model_class;
			$entries = $class::browse();

			// failed
			if ( $entries === false ) {
				return false;
			}

			// store loaded data
			$this->_setLoadedData( $entries );
			return true;
		}

		// TODO: potentially support mapping load? probably not worth it

		Api::getInstance()->raiseError('Unable To Load','No sub-API path defined.');
		return false;
	}

	protected function _assertLoad() {
		if ( $this->_loaded ) return true;
		return $this->_load();
	}

	/// OVERWRITE
	public function getIterator() {
		if ( !$this->_assertLoad() ) return new \EmptyIterator();
		return parent::getIterator();
	}

	public function offsetExists($offset) {
		if ( !$this->_assertLoad() ) return false;
		return parent::offsetExists( $offset );
	}

	public function offsetGet($offset) {
		if ( !$this->_assertLoad() ) return null;
		return parent::offsetGet( $offset );
	}

	public function offsetSet($offset, $value) {
		if ( $offset !== null && isset( $this->_data[ $offset ] ) ) {
			// no change needed
			if ( $this->_data[ $offset ] === $value ) {
				return;
			}

			Api::getInstance()->raiseError('Unable to Overwrite Reference','You can either modify the existing entry, or remove and replace it (among many other options).');
			return;
		}

		if ( !$this->_definition->getSubApiPath() && !$this->_definition->getMapping() ) {
            Api::getInstance()->raiseError('Writing Not Supported','The reference ' . $this->_definition->getName() . ' does not support writing values.');
			return;
		}

		// check mode class
		if ( !is_a( $value , $this->_model_class ) ) {
            Api::getInstance()->raiseError('Expecting ' . $this->_model_class,'You can only add values of the expected type to references.');
			return;
		}

		/** @var Model $value */

		// already exists
		if ( $value->doesExist() ) {
            Api::getInstance()->raiseError('Expecting New ' . $this->_model_class,'You can only unsaved entries to references.');
			return;
		}

		// set reference
		$value->setParentReference( $this->_parent , $this->_definition );

		// no sub api path? we must manually inject the mapping values here
		if ( !$this->_definition->getSubApiPath() ) {
			// use the mapping to add necessary values
			foreach ( $this->_definition->getMapping() as $parent => $local ) {
				$value->$local = $this->_parent->$parent;
			}
		}

		if ( $this->_parent->doesExist() ) {
			// insert it now
			if ( $value = $value->insert() ) {
				$this->_data[] = $value;
			}
		}
		else {
			// insert it later
			$this->_data[] = $value;
		}
	}

	public function insertAll() {
		foreach ( $this->_data as $entry ) {
			if ( $entry->doesExist() ) continue;
			$entry->insert();
		}
	}

//	public function offsetUnset($offset) {
//		ArcticAPI::getInstance()->raiseError('Invalid Access','You can not write to model sets.');
//	}

	/**
	 * @return int
	 */
	public function getTotal() {
		if ( !$this->_assertLoad() ) return 0;
		return parent::getTotal();
	}

	public function count() {
		if ( !$this->_assertLoad() ) return 0;
		return parent::count();
	}
}
