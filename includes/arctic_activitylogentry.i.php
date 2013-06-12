<?php

/**
 * Class Arctic_ActivityLogEntry
 * Read only! Except for dismiss function.
 * @property int $businessgroupid
 * @property int|null $agentid
 * @property int $id
 * @property string $type
 * @property string $description
 * @property int $severity
 * @property bool $pending
 * @property DateTime $time
 * @property DateTime|null $dismissedon
 * @property string|null $dismissedbyagentid
 * @property Arctic_BusinessGroup $businessgroup
 * @method dismiss()
 */
class Arctic_ActivityLogEntry extends ArcticModel
{
	public static function getApiPath() {
		return 'activitylog';
	}

	protected static function _mapMethod( $method ) {
		if ( $method === 'dismiss' ) {
			return new ArcticModelMethod( ArcticModelMethod::TYPE_EXISTING_MODEL , 'DISMISS' );
		}
		return ArcticModel::_mapMethod( $method );
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference( 'businessgroup' , 'Arctic_BusinessGroup' , array( 'businessgroupid' => 'id' ) );
	}
}