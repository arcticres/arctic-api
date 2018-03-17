<?php

namespace Arctic\Model\Person;

use Arctic\Api;
use Arctic\Method\Insert;
use Arctic\Method\Method;
use Arctic\Model;

class _MethodInsertOrUpdate extends Insert
{
	public function __construct() {
		parent::__construct();
		$this->_uri = 'create-or-update';
		$this->_argument_mapping = array('confidence');
	}

	protected function _getRequestData() {
		/** @var Person $person */
		$person = $this->_model;

		// convert to array
		$body = $this->_model->toArray();

		// pull in references
		if (count($person->emailaddresses)) {
			$body['emailaddress'] = $person->emailaddresses[0]->emailaddress;
		}
		if (count($person->phonenumbers)) {
			switch (strtolower($person->phonenumbers[0]->type)) {
				case 'mobile':
				case 'cell':
					$key = 'phonemobile';
					break;
				case 'home':
					$key = 'phonehome';
					break;
				case 'work':
				case 'business':
					$key = 'phonework';
					break;
				default:
					$key = 'phonenumber';
					break;
			}
			$body[$key] = $person->phonenumbers[0]->phonenumber;
		}

		return $body;
	}

	/**
	 * Overrides the normal request format, to include email and phone fields directly in the request.
	 * This enables better deduplication
	 * @param $api_path
	 * @param $arguments
	 * @return bool
	 */
	protected function _prepareRequest($api_path, $arguments) {
		if ($this->_model->doesExist()) {
			Api::getInstance()->raiseError('Model Already Saved','Cannot be inserted again.');
		}

		// encode arguments and build URL
		$url = $this->_buildUrl($api_path, $arguments);

		// build uri
		return $this->_runRequest($url, $this->_method, json_encode($this->_getRequestData()));
	}

	protected function _insertNewEmailAddresses(\Arctic\Reference\SetWrapper $potential_email_addresses) {
		/** @var Person $person */
		$person = $this->_model;

		$existing = array();
		foreach ($person->emailaddresses as $ea) {
			$existing[] = strtolower($ea->emailaddress);
		}

		foreach ($potential_email_addresses->getRawData() as $ea) {
			/** @var EmailAddress $ea */
			if ($ea->doesExist()) continue;
			if (in_array(strtolower($ea->emailaddress), $existing)) continue;
			$ea->insert();
		}
	}

	protected function _insertNewPhoneNumbers(\Arctic\Reference\SetWrapper $potential_phone_numbers) {
		/** @var Person $person */
		$person = $this->_model;

		$existing = array();
		foreach ($person->phonenumbers as $pn) {
			$existing[] = preg_replace('/[^x0-9]+/', '', $pn->phonenumber);
		}

		foreach ($potential_phone_numbers->getRawData() as $pn) {
			/** @var PhoneNumber $pn */
			if ($pn->doesExist()) continue;
			if (in_array(preg_replace('/[^x0-9]+/', '', $pn->phonenumber), $existing)) continue;
			$pn->insert();
		}
	}

	protected function _describeAddress(Address $addr) {
		if (preg_match('/\d+/', sprintf('%s %s', $addr->address1, $addr->address2), $match)) {
			$number = $match[0];
		}
		else {
			$number = '';
		}

		$pc = $addr->postalcode;

		return sprintf('%s:%s', $number, $pc);
	}

	protected function _insertNewAddresses(\Arctic\Reference\SetWrapper $potential_addresses) {
		/** @var Person $person */
		$person = $this->_model;

		$existing = array();
		foreach ($person->addresses as $addr) {
			$existing[] = $this->_describeAddress($addr);
		}

		foreach ($potential_addresses->getRawData() as $addr) {
			/** @var Address $addr */
			if ($addr->doesExist()) continue;
			if (in_array($this->_describeAddress($addr), $existing)) continue;
			$addr->insert();
		}
	}

	/**
	 * Overrides some of the normal parsing response, to better determine
	 * @param array $response
	 * @return Model
	 */
	protected function _parseResponse( $response ) {
		// get references (before filling data)
		$references = $this->_model->getReferences();

		// fill data
		$this->_model->fillExistingData( isset( $response[ 'id' ] ) ? $response[ 'id' ] : null , $response );

		// update cache if an ID was returned
		if (isset($response['id'])) {
			Api::getInstance()->getCacheManager()->set($response['id'], $response, $this->_model_class);
		}

		// write references BUT ONLY IF THEY DO NOT EXIST
		foreach ($references as $name => $obj) {
			if (isset($response[$name]) && count($response[$name])) {
				switch ($name) {
					case 'emailaddresses':
						$this->_insertNewEmailAddresses($obj);
						break;
					case 'phonenumbers':
						$this->_insertNewPhoneNumbers($obj);
						break;
					case 'addresses':
						$this->_insertNewAddresses($obj);
						break;
					default:
						// don't insert
				}
			}
			else {
				// standard approach... insert everything
				if ($obj instanceof \Arctic\Reference\SetWrapper) {
					$obj->insertAll();
				}
				else {
					$obj->insert();
				}
			}
		}

		return $this->_model;
	}
}

class _MethodMerge extends Method
{
	public function __construct() {
		parent::__construct(Method::TYPE_EXISTING_MODEL, Api::METHOD_POST, 'merge', array('personid'));
	}

	/**
	 * @param array $response
	 * @return Model
	 */
	protected function _parseResponse($response) {
		// reload model data
		$this->_model->fillExistingData($this->_model->getID() , $response);

		// update cache if an ID was returned
		if ($id = $this->_model->getID()) {
			Api::getInstance()->getCacheManager()->set($id, $response, $this->_model_class);
		}

		return $this->_model;
	}
}

/**
 * Class Person
 * @property int $id
 * @property string $namefirst
 * @property string $namelast
 * @property string $namecompany
 * @property bool $iscustomer
 * @property bool $isuser
 * @property bool $isguide
 * @property bool $isbookingagent
 * @property bool $isvendor
 * @property string $customersource
 * @property string $gender
 * @property string $birthday
 * @property int $dependentofpersonid
 * @property string $createdon
 * @property string $modifiedon
 * @property EmailAddress[] $emailaddresses
 * @property Address[] $addresses
 * @property PhoneNumber[] $phonenumbers
 * @property Note[] $notes
 * @method insertOrUpdate($confidence=0.7)
 * @method email(int $templateid=null,bool $outbox=false)
 * @method link(int $siteid=null)
 * @method merge(int|array $personid) Merge person(s) represented by $personid into the current record.
 */
class Person extends Model
{
	public static function getApiPath() {
		return 'person';
	}

	public function __construct() {
		parent::__construct();

		$this->_addMultipleReference('emailaddresses', __NAMESPACE__ . '\EmailAddress', 'emailaddress', array( 'id' => 'personid' ) );
		$this->_addMultipleReference('addresses', __NAMESPACE__ . '\Address', 'address' , array( 'id' => 'personid' ) );
		$this->_addMultipleReference('phonenumbers', __NAMESPACE__ . '\PhoneNumber', 'phonenumber' , array( 'id' => 'personid' ) );
		$this->_addMultipleReference('notes', __NAMESPACE__ . '\Note', 'note', array( 'id' => 'personid' ) );
	}

    protected static function _mapMethod( $method ) {
	    // person specific method: insertOrUpdate($confidence)
	    if ($method === 'insertOrUpdate') {
		    return new _MethodInsertOrUpdate();
	    }

        // person specific method: email($templateid=null, $outbox=false)
        if ( $method === 'email' ) {
            return new Method(Method::TYPE_EXISTING_MODEL, Api::METHOD_POST, 'email', array('templateid' , 'outbox'));
        }

        // person specific method: link($siteid=null)
        if ( $method === 'link' ) {
            return new Method(Method::TYPE_EXISTING_MODEL, Api::METHOD_GET, 'link', array('siteid'));
        }

        // person specific method: merge($personid)
        if ($method === 'merge') {
            return new _MethodMerge();
        }

        return parent::_mapMethod($method);
    }
}
