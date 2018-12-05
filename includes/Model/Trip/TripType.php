<?php

namespace Arctic\Model\Trip;

use Arctic\Model;
use Arctic\Method\Method;

class _MethodAvailability extends Method
{
	public function __construct() {
		parent::__construct(Method::TYPE_EXISTING_MODEL, \Arctic\Api::METHOD_POST, 'availability');
	}

	protected function _prepareRequest($api_path, $arguments) {
		if (empty($arguments)) throw new \Arctic\Exception\BadRequest('Trip availability data is required.');

		// body
		$body = array_shift($arguments);
		if (!is_array($body)) throw new \Arctic\Exception\BadRequest('Expected an associative array for type availability data.');

		// encode arguments and build URL
		$url = $this->_buildUrl($api_path, $arguments);

		// encode body
		return $this->_runRequest($url, $this->_method, json_encode($body));
	}

	protected function _parseResponse($response) {
		return $response;
	}
}

/**
 * Class TripType
 * @property int $businessgroupid
 * @property int $parenttripid
 * @property int $id
 * @property string $starttime
 * @property string $subtripstartoffset
 * @property string $name
 * @property string $shortname
 * @property string $color
 * @property int $openings
 * @property string $duration
 * @property int $registrationformid
 * @property bool $orenable
 * @property string $orname
 * @property string $ordescription
 * @property string $ordetails
 * @property int $orimageid
 * @property int $orminimumguests
 * @property string $orcutoff
 * @property int $registrationcutoffdays
 * @property bool $attachsameasparent
 * @property string $notes
 * @property int $accountid
 * @property int $paymentplanid
 * @property int $cancellationpolicyid
 * @property string $createdon
 * @property string $modifiedon
 * @property bool $deleted
 * @property \Arctic\Model\BusinessGroup $businessgroup
 * @property PricingLevel[] $pricinglevels
 * @property Component[] $components
 * @method array updateAvailability(array $data)
 */
class TripType extends Model
{
	public static function getApiPath() {
		return 'triptype';
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference( 'businessgroup' , 'Arctic\Model\BusinessGroup' , array( 'businessgroupid' => 'id' ) );
		$this->_addMultipleReference('pricinglevels', __NAMESPACE__ . '\PricingLevel' , 'pricinglevel' );
		$this->_addMultipleReference('components', __NAMESPACE__ . '\Component' , 'component' );
	}

	protected static function _mapMethod($method) {
		// trip type specific method: availability(array $data)
		if ('updateAvailability' === $method) {
			return new _MethodAvailability();
		}

		return parent::_mapMethod($method);
	}
}
