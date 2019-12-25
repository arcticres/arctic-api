<?php

namespace Arctic\Model\Activity;

use Arctic\Model;

class _MethodStatus extends \Arctic\Method\Method
{
	public function __construct() {
		parent::__construct(self::TYPE_EXISTING_MODEL, \Arctic\Api::METHOD_POST, 'status');
	}

	protected function _prepareRequest( $api_path , $arguments ) {
		$request = [
			'status' => $arguments[0]
		];
		if (isset($arguments[1])) {
			$request['cancellation_fee'] = $arguments[1];
		}
		if (isset($arguments[2])) {
			$request['preserve_commissions'] = $arguments[2];
		}

		// encode arguments and build URL
		$url = $this->_buildUrl($api_path, $arguments);

		// run the request
		return $this->_runRequest($url, $this->_method, json_encode($request));
	}

	protected function _parseResponse($response) {
		// reload model data
		$this->_model->fillExistingData( $this->_model->getID()  , $response );

		// update cache if an ID was returned
		if ($id = $this->_model->getID()) {
			\Arctic\Api::getInstance()->getCacheManager()->set($id, $response, $this->_model_class);
		}

		return $this->_model;
	}
}

/**
 * @class Activity
 * @property int $businessgroupid
 * @property int $parentactivityid
 * @property int $id
 * @property string $type
 * @property string $name
 * @property string $start
 * @property string $end
 * @property int $personid
 * @property int $bookingagentid
 * @property int $invoiceid
 * @property int $invoiceitemgroupid
 * @property int $packageid
 * @property bool $isgroup
 * @property string $groupmode
 * @property string $groupinvoice
 * @property array|null $grouppricing
 * @property array|null $groupholds
 * @property string|null $groupholdsexpire
 * @property bool $madeonline
 * @property string $source
 * @property string $promocode
 * @property string $status
 * @property string $createdon
 * @property string $modifiedon
 * @property int $createdbyuserid
 * @property bool $deleted
 * @property string $manageurl
 * @property \Arctic\Model\Activity\Activity $parentactivity
 * @property \Arctic\Model\Activity\Activity[] $subactivities
 * @property \Arctic\Model\Invoice\Invoice $invoice
 * @property \Arctic\Model\Person\Person $person
 * @property \Arctic\Model\Person\Person $bookingagent
 * @method setStatus($status, $cancellation_fee=null, $preserve_commissions=false)
 */
class Activity extends Model
{
	const STATUS_PENDING = 'pending';
	const STATUS_UNFINISHED = 'unfinished';
	const STATUS_FINISHED = 'finished';
	const STATUS_CANCELED = 'canceled';
	const STATUS_NOSHOW = 'noshow';
	const STATUS_OVER = 'over';

	const GROUP_MODE_BURSTABLE = 'burstable';
	const GROUP_MODE_FIXED = 'fixed';

	const GROUP_INVOICE_SEPARATE = 'separate';
	const GROUP_INVOICE_SHARED = 'shared';

	public static function getApiPath() {
		return 'activity';
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference('invoice', 'Arctic\Model\Invoice\Invoice', array('invoiceid'=>'id'));
		$this->_addSingleReference('person', 'Arctic\Model\Person\Person', array('personid'=>'id'));
		$this->_addSingleReference('bookingagent', 'Arctic\Model\Person\Person', array('bookingagentid'=>'id'));
		$this->_addSingleReference('parentactivity', __NAMESPACE__ . '\Activity', array('parentactivityid' => 'id'));
		$this->_addMultipleReference('subactivities', __NAMESPACE__ . '\Activity', array('id' => 'parentactivityid'));
	}

	protected static function _mapMethod( $method ) {
		// activity specific method: setStatus($status=null, $cancellation_fee=null, $preserve_commissions=false)
		if ( $method === 'setStatus' ) {
			return new _MethodStatus();
		}

		return parent::_mapMethod($method);
	}
}
