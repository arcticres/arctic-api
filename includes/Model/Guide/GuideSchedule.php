<?php

namespace Arctic\Model\Guide;

use Arctic\Model;

/**
 * Class GuideSchedule
 * @property int $guideid
 * @property int $id
 * @property int $tripid
 * @property string|null $start
 * @property string|null $end
 * @property string $notes
 * @property string $createdon
 * @property string $modifiedon
 * @property bool $deleted
 * @property Guide $guide
 */
class GuideSchedule extends Model
{
	public static function getApiPath() {
		// currently does not have a direct api call
		// just accessed as a subobject of trips
		return null;
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference('guide', __NAMESPACE__ . '\Guide', array('guideid' => 'id'));
	}
}
