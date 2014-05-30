<?php

namespace Arctic\Model\Store;

use Arctic\Model;

/**
 * Class Payment
 * @property float $amount
 * @property string $url_success
 * @property string $url_failure
 * @property string $note
 * @property int $personid
 * @property string $namefirst
 * @property string $namelast
 * @property string $address1
 * @property string $address2
 * @property string $postalcode
 * @property bool $kiosk
 * @property bool $card_reader
 * @property int $invoiceid
 * @property int $invoicetransactionid
 * @property int $businessgroupid
 * @property string $status
 * @property int $started
 * @property int $expires
 */
class Payment extends Model
{
    public static function getApiPath() {
        return 'payment';
    }

    public function __construct() {
        parent::__construct();
    }

    /**
     * Prevent cache.
     * @return bool
     */
    public static function getCacheProfile() {
        return false;
    }
}
