<?php

namespace Arctic\Model\TripForm;

use Arctic\Model;

/**
 * Class Response
 * @property int $businessgroupid
 * @property int $triptripformid
 * @property int $tripformformid
 * @property int $id
 * @property int $tripid
 * @property int $personid
 * @property DateTime $time
 * @property \Arctic\Model\Trip\Trip $trip
 * @property \Arctic\Model\BusinessGroup $businessgroup
 * @property \Arctic\Model\Person\Person $person
 */
class Response extends Model
{
	public static function getApiPath() {
		return 'tripformresponse';
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference( 'businessgroup' , 'Arctic\Model\BusinessGroup' , array( 'businessgroupid' => 'id' ) );
		$this->_addSingleReference( 'person' , 'Arctic\Model\Person\Person' , array( 'personid' => 'id' ) );
		$this->_addSingleReference( 'trip' , 'Arctic\Model\Trip\Trip' , array( 'tripid' => 'id' ) );
	}
}
