<?php

namespace Arctic\Model\Evaluation;

use Arctic\Model;

/**
 * Class Response
 * @property int $businessgroupid
 * @property int $evaluationid
 * @property int $id
 * @property int $personid
 * @property int $activityid
 * @property string $useragent
 * @property \DateTime $time
 * @property \Arctic\Model\Activity\Activity $activity
 * @property \Arctic\Model\BusinessGroup $businessgroup
 * @property \Arctic\Model\Person\Person $person
 */
class Response extends Model
{
	public static function getApiPath() {
		return 'evaluationresponse';
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference( 'businessgroup' , 'Arctic\Model\BusinessGroup' , array( 'businessgroupid' => 'id' ) );
		$this->_addSingleReference( 'person' , 'Arctic\Model\Person\Person' , array( 'personid' => 'id' ) );
		$this->_addSingleReference( 'activity' , 'Arctic\Model\Activity\Activity' , array( 'activityid' => 'id' ) );
	}
}
