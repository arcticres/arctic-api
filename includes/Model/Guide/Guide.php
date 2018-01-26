<?php

namespace Arctic\Model\Guide;

use Arctic\Model;

/**
 * Class Guide
 * @property int $businessgroupid
 * @property int $personid
 * @property int $id
 * @property string $schedulingnotes
 * @property string $createdon
 * @property string $modifiedon
 * @property bool $deleted
 * @property \Arctic\Model\Person\Person $person
 */
class Guide extends Model
{
	public static function getApiPath() {
		return 'guide';
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference('person', 'Arctic\Model\Person\Person', array('personid' => 'id'));
	}
}
