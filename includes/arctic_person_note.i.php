<?php

/**
 * Class Arctic_Person_Note
 * @property int $personid
 * @property int $id
 * @property string $note
 * @property string $createdon
 * @property string $modifiedon
 */
class Arctic_Person_Note extends ArcticModel
{
	public static function getApiPath() {
		// currently does not have a direct api call
		// just accessed as a subobject of persons
		return null;
	}

	public function __construct() {
		parent::__construct();
	}
}
