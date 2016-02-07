<?php

namespace Arctic;

/**
 * Class ArcticException
 * @method static static[] browse($start=0,$number=50)
 * @method static static[] query($query)
 * @method static static load($id)
 * @method bool insert()
 * @method bool update()
 * @method bool delete()
 */
class Model
{
	protected $_id;
	protected $_exists = false;
	protected $_data = array();
	protected $_new_data = array();

	/**
	 * @var Reference\Definition[]
	 */
	protected $_reference_definitions = array();

	protected $_references = array();

	/**
	 * @var Model
	 */
	protected $_parent;

	/**
	 * @var Reference\Definition
	 */
	protected $_parent_reference;

	protected static $_relative_api_path;

	public static function getApiPath() {
		$class = get_called_class();
		if ( $p = strpos( $class , '_' ) ) {
			return strtolower( substr( $class , $p ) );
		}
		return strtolower( $class );
	}

	/**
	 * @internal
	 * @return string
	 */
	public static function getRelativeApiPath() {
		if ( isset( self::$_relative_api_path ) ) {
			$ret = self::$_relative_api_path;
			self::$_relative_api_path = null;;
			return $ret;
		}

		return static::getApiPath();
	}

	/**
	 * Static calls do not have a parent reference context. This is used to temporarily force a context. It will only
	 * apply to the next static call.
	 * @internal
	 * @param $api
	 */
	public static function forceRelativeApiPath( $api ) {
		self::$_relative_api_path = $api;
	}

	/**
	 * @param string $method
	 * @return Method\Method
	 */
	protected static function _mapMethod( $method ) {
		switch ( $method ) {
			case 'browse':
				return new Method\Browse();
			case 'query':
				return new Method\Query();
			case 'load':
				return new Method\Load();
			case 'insert':
				return new Method\Insert();
			case 'update':
				return new Method\Update();
			case 'delete':
				return new Method\Delete();
			default:
				return false;
		}
	}

	public function __construct() {

	}

	public function getMyRelativeApiPath() {
		// build uri
		if ( isset( $this->_parent ) ) {
			if ( $sub_api_path = $this->_parent_reference->getSubApiPath() ) {
				$path = $this->_parent->getMyRelativeApiPath() . '/' . $sub_api_path;
			}
			else {
				$path = static::getApiPath();
			}
		}
		else {
			$path = static::getApiPath();
		}

		// add model id
		if ( $this->doesExist() ) {
			if ( $id = $this->getID() ) {
				$path .= '/' . $id;
			}
			else {
				Api::getInstance()->raiseError('No ID Defined','Unable to determine the ID of an existing model.');
				return false;
			}
		}

		return $path;
	}

	/**
	 * Limited: currently, never uses sub-api. Sub-api does not currently support
	 * single references.
	 * @param $name
	 * @param $class
	 * @param array $mapping
	 */
	protected function _addSingleReference( $name , $class , array $mapping=null ) {
		$this->_reference_definitions[ $name ] = new Reference\Definition(
			Reference\Definition::TYPE_SINGLE ,
			$name ,
			$class ,
			null ,
			$mapping
		);
	}

	/**
	 * ALWAYS uses sub-api.
	 * @param $name
	 * @param $class
	 * @param string $sub_api_path
	 * @param array $mapping
	 */
	protected function _addMultipleReference( $name , $class , $sub_api_path , array $mapping=null ) {
		$this->_reference_definitions[ $name ] = new Reference\Definition(
			Reference\Definition::TYPE_MULTIPLE ,
			$name ,
			$class ,
			$sub_api_path ,
			$mapping
		);
	}

	/**
	 * @param Model $parent
	 * @param Reference\Definition $reference
	 */
	public function setParentReference( Model $parent , Reference\Definition $reference ) {
		$this->_parent = $parent;
		$this->_parent_reference = $reference;
	}

	/**
	 * @return mixed
	 */
	public function getParentReference() {
		return $this->_parent_reference;
	}

	/**
	 * @return boolean
	 */
	public function doesExist() {
		return $this->_exists;
	}

	/**
	 * @return mixed
	 */
	public function getID() {
		return $this->_id;
	}

	/**
	 * Get an array representation of the object (does not include references).
	 * @return array
	 */
	public function toArray() {
		return array_merge( $this->_data , $this->_new_data );
	}

	/**
	 * Return changes that are pending and will be saved or inserted.
	 * @return array
	 */
	public function delta() {
		$ret = array();
		foreach ( $this->_new_data as $key => $new ) {
			$old = ( isset( $this->_data[ $key ] ) ? $this->_data[ $key ] : null );
			$ret[ $key ] = array( $old , $new );
		}
		return $ret;
	}

	/**
	 * Get all reference definitions.
	 * @return Reference\Definition[]
	 */
	public function getReferenceDefinitions() {
		return $this->_reference_definitions;
	}

	/**
	 * Get all references.
	 * @return array
	 */
	public function getReferences() {
		return $this->_references;
	}

	/**
	 * @param string $method
	 * @param array $arguments
	 * @return mixed
	 * @throws \BadMethodCallException
	 */
	public function __call( $method , $arguments ) {
		$method = static::_mapMethod( $method );
		if ( $method === false ) {
			throw new \BadMethodCallException('Method does not exist: ' . $method . '.' );
		}

		// pass self
		$method->setModel( $this );

		// build path
		$path = $this->getMyRelativeApiPath();
		if ( $path === false ) return false;

		return $method->runRequest( $path , $arguments );
	}

	/**
	 * @internal
	 * @param string $method
	 * @param array $arguments
	 * @return mixed
	 * @throws \BadMethodCallException
	 */
	public static function __callStatic( $method , $arguments ) {
		$method = static::_mapMethod( $method );
		if ( $method === false ) {
			throw new \BadMethodCallException('Method does not exist: ' . $method . '.' );
		}

		// pass model class
		$method->setModel( get_called_class() );

		return $method->runRequest( static::getRelativeApiPath() , $arguments );
	}

	/**
	 * @internal
	 * @param string $name
	 * @return mixed|null
	 */
	public function __get( $name ) {
		// check references
		if ( isset( $this->_reference_definitions[ $name ] ) ) {
			// initiate blank
			if ( !isset( $this->_references[ $name ] ) ) {
				$this->_references[ $name ] = $this->_reference_definitions[ $name ]->initiateBlankReference( $this );
			}

			return $this->_references[ $name ];
		}

		// read new data
		if ( array_key_exists( $name , $this->_new_data ) ) {
			return $this->_new_data[ $name ];
		}

		// read existing data
		if ( array_key_exists( $name , $this->_data ) ) {
			return $this->_data[ $name ];
		}

		return null;
	}

	/**
	 * @internal
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set( $name , $value ) {
		// no change required?
		if ( isset( $this->_data[ $name ] ) && $this->_data[ $name ] == $value ) {
			unset( $this->_new_data[ $name ] );
			return;
		}

		// can not write to references
		if ( isset( $this->_reference_definitions[ $name ] ) ) {
			Api::getInstance()->raiseError('Unable to Set Reference','Reference can not be directly set. Modify the reference object, or edit the reference id column.');
		}

		$this->_new_data[ $name ] = $value;
	}

	/**
	 * @internal
	 * @param string $name
	 * @return bool
	 */
	public function __isset( $name ) {
		// is reference set
		if ( isset( $this->_reference_definitions[ $name ] ) ) {
			return $this->_reference_definitions[ $name ]->isReferenceSet( $this->_references[ $name ] );
		}

		// is in new data
		if ( array_key_exists( $name , $this->_new_data ) ) {
			return ( $this->_new_data[ $name ] !== null );
		}

		// is in existing data
		if ( array_key_exists( $name , $this->_data ) ) {
			return ( $this->_data[ $name ] !== null );
		}

		return false;
	}

	/**
	 * @internal
	 * @param string $name
	 */
	public function __unset( $name ) {
		if ( isset( $this->_reference_definitions[ $name ] ) ) {
			Api::getInstance()->raiseError('Unable to Unset Reference','Reference can not be unset. Delete the reference object, or edit the reference id column.');
		}

		// clear if exists in _data
		if ( array_key_exists( $name , $this->_data ) ) {
			if ( $this->_data[ $name ] !== null ) {
				$this->_new_data[ $name ] = null;
			}
			return;
		}
	}

	/**
	 * @internal
	 * @param mixed $id
	 * @param array $data
	 */
	public function fillExistingData($id, $data) {
		$this->_id = $id;
		$this->_exists = true;
		$this->_data = $data;
		$this->_new_data = array();

		// move references out of data
		foreach ( $this->_reference_definitions as $name => $definition ) {
			// unset it, in case existing data was loaded that may have been invalidated
			unset( $this->_references[ $name ] );

			// no new data, skip it
			if ( !isset( $this->_data[ $name ] ) ) {
				$this->_references[ $name ] = $definition->initiateBlankReference( $this );
				continue;
			}

			// move it
			$ref_data = $this->_data[ $name ];
			unset( $this->_data[ $name ] );

			// initiate reference data
			$this->_references[ $name ] = $definition->initiateReferenceData( $this , $ref_data );
		}
	}
}
