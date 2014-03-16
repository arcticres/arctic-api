<?php

namespace Arctic\Model\Person;

use Arctic\Model;

/**
 * Class Address
 * @property int $personid
 * @property int $id
 * @property string $type
 * @property bool $isprimary
 * @property string $address1
 * @property string $address2
 * @property string $city
 * @property string $state
 * @property string $postalcode
 * @property int $countryid
 * @property bool $subscribetomaillist
 * @property string $createdon
 * @property string $modifiedon
 * @property \Arctic\Model\Country $country
 */
class Address extends Model
{
	public static function getApiPath() {
		// currently does not have a direct api call
		// just accessed as a subobject of persons
		return null;
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference('country', 'Arctic\Model\Country', array('countryid'=>'id'));
	}
}
