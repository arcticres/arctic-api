<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// Availability data for rental items is available for a specified date range (or date time range), up to 45 days. The
// availability data is an array of changes in availability. There will always be at least one entry in the array
// representing the initial availability at the start of the time period.

// Format:
//  array(
//      array(
//          'type'      =>  'initial', // OR 'alloc' OR 'adjust'
//          'time'      =>  '2015-01-01 00:00:00',
//          'quantity'  =>  10
//      ),
//      array(
//          'type'      =>  'alloc', // an allocation (a rental or reservation)
//          'time'      =>  '2015-01-22 13:30:00',
//          'delta'     =>  -2, // the effect this has on the quantity available (only available for alloc or adjust)
//          'quantity'  =>  8 // new quantity available, equal to old + delta
//      )
//  );

// EXAMPLE 1: get availability data for single item

// load rental item #3
$rental_item = \Arctic\Model\Rental\Item::load(3);
var_dump($rental_item->availability());

// EXAMPLE 2: get availability data for many items, all as part of a single query

// get availability throughout a business group
// including the extra query parameters will cause all rental items to be returned with an availability data
// structure showing usage over the time period
$rental_items = \Arctic\Model\Rental\Item::query('businessgroupid = 3', array(
	'available_start' => '2015-01-01',
	'available_end' => '2015-02-01'
));
foreach ($rental_items as $rental_item) {
	echo 'AVAILABILITY: ', $rental_item->name, PHP_EOL;
	var_dump($rental_item->availability);
	echo PHP_EOL;
}
