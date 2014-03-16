<?php

namespace Arctic\Model;

use Arctic\Model;

/**
 * Class Country
 * Read only!
 * @property int $id
 * @property string $name
 * @property string $twodigitcode
 * @property string $threedigitcode
 * @property string $numericcode
 * @property string $phonecode
 * @property string $postalcodemask
 * @property string $phonenumbermask
 */
class Country extends Model
{
	public static function getApiPath() {
		return 'country';
	}

	public function __construct() {
		parent::__construct();
	}

    /**
     * Cache indefinitely.
     * @return bool
     */
    public static function getCacheProfile() {
        return true;
    }
}
