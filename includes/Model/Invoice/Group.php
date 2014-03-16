<?php

namespace Arctic\Model\Invoice;

use Arctic\Model;

/**
 * Class Group
 * @property int $businessgroupid
 * @property int $invoiceid
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $order
 * @property float $totalcost
 * @property string|null $activitystart
 * @property string|null $activityend
 * @property string|null $accrueon
 * @property string $createdon
 * @property string $modifiedon
 * @property Item[] $items
 */
class Group extends Model
{
    public static function getApiPath() {
        // currently does not have a direct api call
        // just accessed as a subobject of persons
        return null;
    }

    public function __construct() {
        parent::__construct();

        $this->_addMultipleReference('items', __NAMESPACE__ . '\Item', 'item', array(
            'invoiceid'=>'invoiceid',
            'id'=>'invoiceitemgroupid')
        );
    }
}
