<?php

class ArcticReferenceSetWrapper extends ArcticModelSet
{
	protected $_loaded = false;

	/**
	 * @var ArcticModel
	 */
	protected $_parent;

	/**
	 * @var string
	 */
	protected $_model_class;

	/**
	 * @var ArcticModelReferenceDefinition
	 */
	protected $_definition;

	public function __construct( $parent , $class_or_array , ArcticModelReferenceDefinition $definition ) {
		parent::__construct( $definition->getModelClass() , array( 'entries' => array() ) );

		// potential memory leak issue
		$this->_parent = $parent;

		// use sub api path
		$this->_definition = $definition;

		// set object
		if ( is_array( $class_or_array ) ) {
			$this->_model_class = $this->_definition->getModelClass();
			$this->_setLoadedData( $class_or_array );
		}
		else {
			$this->_model_class = $class_or_array;
		}

	}

	/**
	 * @param ArcticModel[] $entries
	 */
	protected function _setLoadedData( $entries ) {
		$this->_loaded = true;
		$this->_data = array();

		foreach ( $entries as $entry ) {
			// still an array? turn it into an object
			if ( !is_object( $entry ) ) {
				$class = $this->_model_class;

				/** @var ArcticModel $obj */
				$obj = new $class();
				$obj->fillExistingData( isset( $entry[ 'id' ] ) ? $entry[ 'id' ] : null , $entry );
				$entry = $obj;
			}

			$entry->setParentReference( $this->_parent , $this->_definition );
			$this->_data[] = $entry;
		}
	}

	protected function _load() {
		if ( $sub_api_path = $this->_definition->getSubApiPath() ) {
			// prefix path
			ArcticModel::forceRelativeApiPath( $this->_parent->getMyRelativeApiPath() . '/' . $sub_api_path );

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

		ArcticAPI::getInstance()->raiseError('Unable To Load','No sub-API path defined.');
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

			ArcticAPI::getInstance()->raiseError('Unable to Overwrite Reference','You can either modify the existing entry, or remove and replace it (among many other options).');
			return;
		}

		if ( !$this->_definition->getSubApiPath() && !$this->_definition->getMapping() ) {
			ArcticAPI::getInstance()->raiseError('Writing Not Supported','The reference ' . $this->_definition->getName() . ' does not support writing values.');
			return;
		}

		// check mode class
		if ( !is_a( $value , $this->_model_class ) ) {
			ArcticAPI::getInstance()->raiseError('Expecting ' . $this->_model_class,'You can only add values of the expected type to references.');
			return;
		}

		/** @var ArcticModel $value */

		// already exists
		if ( $value->doesExist() ) {
			ArcticAPI::getInstance()->raiseError('Expecting New ' . $this->_model_class,'You can only unsaved entries to references.');
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