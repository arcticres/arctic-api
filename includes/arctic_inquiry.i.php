<?php

/**
 * Class Arctic_Inquiry
 * @property int $businessgroupid
 * @property int $id
 * @property string $personid
 * @property string $mode
 * @property string $notes
 * @property int $assignedagentid
 * @property int $tripid
 * @property string|null $followupon
 * @property DateTime $createdon
 * @property DateTime $modifiedon
 * @property DateTime|null $followedupon
 * @property bool $deleted
 * @property Arctic_Trip $trip
 * @property Arctic_BusinessGroup $businessgroup
 * @property Arctic_Person $person
 */
class Arctic_Inquiry extends ArcticModel
{
	public static function getApiPath() {
		return 'inquiry';
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference( 'businessgroup' , 'Arctic_BusinessGroup' , array( 'businessgroupid' => 'id' ) );
		$this->_addSingleReference( 'person' , 'Arctic_Person' , array( 'personid' => 'id' ) );
		$this->_addSingleReference( 'trip' , 'Arctic_Trip' , array( 'tripid' => 'id' ) );
	}
}
