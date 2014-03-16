<?php

namespace Arctic\Model\Invoice;

use Arctic\Model;

/**
 * Class Item
 * @property int $businessgroupid
 * @property int $invoiceitemgroupid
 * @property int $parentinvoiceitemid
 * @property int $id
 * @property string $description
 * @property string $shortname
 * @property float $quantity
 * @property string $specialquantity
 * @property float $amount
 * @property float $calculatedamount
 * @property int $accountid
 * @property string $type
 * @property bool $iscredit
 * @property bool $isvisible
 * @property bool $includedinparent
 * @property int $order
 * @property string $ordercategory
 * @property string $createdon
 * @property string $modifiedon
 * @property Item[] $subitems
 */
class Item extends Model
{
    const TYPE_COST = 'cost';
    const TYPE_DISCOUNT = 'discount';

    const AMOUNTTYPE_ABSOLUTE = 'absolute';
    const AMOUNTTYPE_PERCENT = 'percent';

    const ORDERCATEGORY_CHARGE = 'charge';
    const ORDERCATEGORY_DISCOUNT = 'discount';
    const ORDERCATEGORY_FEE_TAXED = 'fee';
    const ORDERCATEGORY_TAX = 'tax';
    const ORDERCATEGORY_FEE_UNTAXED = 'untaxed-fee';
    const ORDERCATEGORY_COMMISSION = 'commission';

    public static function getApiPath() {
        // currently does not have a direct api call
        // just accessed as a subobject of persons
        return null;
    }

    public function __construct() {
        parent::__construct();

        $this->_addMultipleReference('subitems', __NAMESPACE__ . '\Item', 'subitem', array(
            'invoiceid'=>'invoiceid',
            'invoiceitemgroupid'=>'invoiceitemgroupid',
            'id'=>'parentinvoiceitemid'
        ));
    }
}
