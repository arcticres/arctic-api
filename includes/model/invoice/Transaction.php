<?php

namespace Arctic\Model\Invoice;
use Arctic\Model;

/**
 * Class Transaction
 * @property int $invoiceid
 * @property int $id
 * @property string $type
 * @property string $description
 * @property float $amount
 * @property string $referenceclass
 * @property int $referenceid
 * @property string $time
 * @property bool $isbatched
 * @property string|null $settled
 * @property int $batchid
 * @property string $createdon
 * @property string $modifiedon
 * @property int $createdbyuserid
 */
class Transaction extends Model
{
    const TYPE_PAYMENT = 'payment';
    const TYPE_SCHEDULED_PAYMENT = 'scheduled-payment'; // for transparent transactions that can be scheduled in the future
    const TYPE_CREDIT = 'credit'; // unused
    const TYPE_REFUND = 'refund';
    const TYPE_HOLD = 'hold'; // hold funds (credit card specific)

    public static function getApiPath() {
        // currently does not have a direct api call
        // just accessed as a subobject of persons
        return null;
    }

    public function __construct() {
        parent::__construct();
    }
}
