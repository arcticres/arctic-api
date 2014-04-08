<?php

namespace Arctic\Model\Invoice;

use Arctic\Api;
use Arctic\Method\Method;
use Arctic\Model;

class _MethodRefresh extends Method
{
    public function __construct() {
        parent::__construct( self::TYPE_EXISTING_MODEL , Api::METHOD_POST , 'refresh' );
    }

    /**
     * @param array $response
     * @return Model
     */
    protected function _parseResponse( $response ) {
        // reload model data
        $this->_model->fillExistingData( $this->_model->getID()  , $response );

        // update cache if an ID was returned
        if ($id = $this->_model->getID()) {
            Api::getInstance()->getCacheManager()->set($id, $response, $this->_model_class);
        }

        return $this->_model;
    }
}

class _MethodPdf extends Method
{
    public function __construct() {
        parent::__construct( self::TYPE_EXISTING_MODEL , Api::METHOD_GET , 'pdf' );
    }

    /**
     * @param array $response
     * @return array
     */
    protected function _parseResponse( $response ) {
        return $response;
    }
}


/**
 * Class Invoice
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
 * @property \Arctic\Model\Person\Person $person
 * @property Group[] $groups
 * @property Item[] $items
 * @property Transaction[] $transactions
 * @method refresh()
 * @method email(int $templateid=null,bool $outbox=false)
 * @method pdf()
 */
class Invoice extends Model
{
    public static function getApiPath() {
        return 'invoice';
    }

    public function __construct() {
        parent::__construct();

        $this->_addSingleReference('person', 'Arctic\Model\Person\Person', array('personid'=>'id'));
        $this->_addMultipleReference('groups', __NAMESPACE__ . '\Group', 'group', array('id'=>'invoiceid'));
        $this->_addMultipleReference('items', __NAMESPACE__ . '\Item', 'item', array('id'=>'invoiceid'));
        $this->_addMultipleReference('transactions', __NAMESPACE__ . '\Transaction', 'transaction', array('id'=>'invoiceid'));
    }

    protected static function _mapMethod( $method ) {
        // invoice specific method: refresh()
        if ( $method === 'refresh' ) {
            return new _MethodRefresh();
        }

        // invoice specific method: email($templateid=null, $outbox=false)
        if ( $method === 'email' ) {
            return new Method(Method::TYPE_EXISTING_MODEL, Api::METHOD_POST, 'email', array('templateid' , 'outbox'));
        }

        // invoice specific method: pdf()
        if ( $method === 'pdf' ) {
            return new _MethodPdf();
        }

        return parent::_mapMethod($method);
    }
}
