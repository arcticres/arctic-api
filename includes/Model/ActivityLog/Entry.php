<?php

namespace Arctic\Model\ActivityLog;

use Arctic\Method\Method;
use Arctic\Model;

/**
 * Class Entry
 * Read only! Except for dismiss function.
 * @property int $businessgroupid
 * @property int|null $agentid
 * @property int $id
 * @property string $type
 * @property string $description
 * @property int $severity
 * @property bool $pending
 * @property \DateTime $time
 * @property \DateTime|null $dismissedon
 * @property string|null $dismissedbyagentid
 * @property \Arctic\Model\BusinessGroup $businessgroup
 * @method dismiss()
 */
class Entry extends Model
{
	public static function getApiPath() {
		return 'activitylog';
	}

	protected static function _mapMethod( $method ) {
		if ( $method === 'dismiss' ) {
			return new Method( Method::TYPE_EXISTING_MODEL , 'DISMISS' );
		}
		return parent::_mapMethod( $method );
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference( 'businessgroup' , 'Arctic\Model\BusinessGroup' , array( 'businessgroupid' => 'id' ) );
	}
}
