<?php

/**
 * Class Arctic_BusinessGroup
 * @property int $parentbusinessgroupid
 * @property int $id
 * @property string $name
 * @property bool $separateretailinventory
 * @property string $createdon
 * @property string $modifiedon
 */
class Arctic_BusinessGroup extends ArcticModel
{
	public static function getApiPath() {
		return 'businessgroup';
	}

	public function __construct() {
		parent::__construct();
	}
}