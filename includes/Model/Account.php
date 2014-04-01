<?php

namespace Arctic\Model;

use Arctic\Model;

/**
 * Class Account
 * @property int $id
 * @property string $name
 * @property float $rate
 * @property int $accountid
 * @property string $createdon
 * @property string $modifiedon
 */
class Account extends Model
{
    public static function getApiPath() {
        return 'account';
    }

    public function __construct() {
        parent::__construct();
    }
}
