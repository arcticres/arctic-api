<?php

namespace Arctic\Model\Task;

use Arctic\Model;

/**
 * Class Task
 * @property int $businessgroupid
 * @property int $id
 * @property string $notes
 * @property int $assignedagentid
 * @property int $tripid
 * @property string|null $dueon
 * @property \DateTime $createdon
 * @property \DateTime $modifiedon
 * @property int $createdbyuserid
 * @property \DateTime|null $completedon
 * @property bool $deleted
 * @property \Arctic\Model\Trip\Trip $trip
 * @property \Arctic\Model\BusinessGroup $businessgroup
 */
class Task extends Model
{
	public static function getApiPath() {
		return 'task';
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference( 'businessgroup' , 'Arctic\Model\BusinessGroup' , array( 'businessgroupid' => 'id' ) );
		$this->_addSingleReference( 'trip' , 'Arctic\Model\Trip\Trip' , array( 'tripid' => 'id' ) );
	}
}
