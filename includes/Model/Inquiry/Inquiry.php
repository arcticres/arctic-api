<?php

namespace Arctic\Model\Inquiry;

use Arctic\Model;

/**
 * Class Inquiry
 * @property int $businessgroupid
 * @property int $id
 * @property string $personid
 * @property string $mode
 * @property string $notes
 * @property int $assignedagentid
 * @property int $assignedpersonid
 * @property int $tripid
 * @property string|null $followupon
 * @property \DateTime $createdon
 * @property \DateTime $modifiedon
 * @property \DateTime|null $followedupon
 * @property bool $deleted
 * @property \Arctic\Model\Trip\Trip $trip
 * @property \Arctic\Model\BusinessGroup $businessgroup
 * @property \Arctic\Model\Person\Person $person
 */
class Inquiry extends Model
{
	public static function getApiPath() {
		return 'inquiry';
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference( 'businessgroup' , 'Arctic\Model\BusinessGroup' , array( 'businessgroupid' => 'id' ) );
		$this->_addSingleReference( 'person' , 'Arctic\Model\Person\Person' , array( 'personid' => 'id' ) );
		$this->_addSingleReference( 'trip' , 'Arctic\Model\Trip\Trip' , array( 'tripid' => 'id' ) );
	}
}
