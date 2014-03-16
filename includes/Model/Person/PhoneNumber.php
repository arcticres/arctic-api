<?php

namespace Arctic\Model\Person;

use Arctic\Model;

/**
 * Class PhoneNumber
 * @property int $personid
 * @property int $id
 * @property string $type
 * @property bool $isprimary
 * @property int $countryid
 * @property string $phonenumber
 * @property string $createdon
 * @property string $modifiedon
 * @property \Arctic\Model\Country $country
 */
class PhoneNumber extends Model
{
	public static function getApiPath() {
		// currently does not have a direct api call
		// just accessed as a subobject of persons
		return null;
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference('country', 'Arctic\Model\Country', array( 'countryid' => 'id' ) );
	}
}
