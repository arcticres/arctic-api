<?php

class Arctic_Invoice__MethodRefresh extends ArcticModelMethod
{
    public function __construct() {
        parent::__construct( self::TYPE_EXISTING_MODEL , ArcticAPI::METHOD_POST , 'refresh' );
    }

    /**
     * @param array $response
     * @return ArcticModel
     */
    protected function _parseResponse( $response ) {
        // reload model data
        $this->_model->fillExistingData( $this->_model->getID()  , $response );

        return $this->_model;
    }
}


/**
 * Class Arctic_Invoice
 * @property int $businessgroupid
 * @property int $id
 * @property int $personid
 * @property float $totalcost
 * @property float $balancedue
 * @property float $nextpaymentamount
 * @property float $nextpaymentdueon
 * @property bool $irreconcilable
 * @property int $paymentplanid
 * @property int $cancellationpolicyid
 * @property string $note
 * @property string $createdon
 * @property string $modifiedon
 * @property Arctic_Person $person
 * @property Arctic_Invoice_Group[] $groups
 * @property Arctic_Invoice_Item[] $items
 * @method refresh()
 */
class Arctic_Invoice extends ArcticModel
{
    public static function getApiPath() {
        return 'invoice';
    }

    public function __construct() {
        parent::__construct();

        $this->_addSingleReference('person' ,'Arctic_Person',array('personid'=>'id'));
        $this->_addMultipleReference('groups','Arctic_Invoice_Group','group',array('id'=>'invoiceid'));
        $this->_addMultipleReference('items','Arctic_Invoice_Item','item',array('id'=>'invoiceid'));
    }

    protected static function _mapMethod( $method ) {
        if ( $method === 'refresh' ) {
            return new Arctic_Invoice__MethodRefresh();
        }
        return parent::_mapMethod( $method );
    }
}
