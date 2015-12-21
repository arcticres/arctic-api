<?php

namespace Arctic\Model\Activity;

use Arctic\Model;

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
}
