<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// Pricing data for a rental item can be queried over a set time period. Arctic will apply minimum durations and
// any dynamic pricing rules, and calculate a cost for the time period. If there are multiple pricing levels, a
// price for each pricing level will be returned.

// Format:
//  array(
//      array(
//          'id'        =>  1, // id for the price (see pricing levels)
//          'name'      =>  'Standard', // name for the price
//          'showonline'=>  true, // whether this price should be visible to guests
//          'price'     =>  16080, // the price in dollars
//          'details'   =>  '240 Weekend ($25.00) hours and 504 Weekday ($20.00) hours' // description of the price ONLY if dynamic pricing
//      )
//  );


// EXAMPLE: get price data

// load rental item #3
$rental_item = \Arctic\Model\Rental\Item::load(1);
var_dump($rental_item->price('2016-01-25', '2016-02-01'));
