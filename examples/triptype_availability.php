<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

$trip_type = \Arctic\Model\Trip\TripType::load(1);
$response = $trip_type->updateAvailability(array(
	'create' => true,
	'update' => true,
	'cancel' => true,
//	'simulate' => true,
	'range' => array(
		'start' => '2019-03-15',
		'end' => '2019-12-15',
	),
	'trips' => array(
		array(
			'start' => '2019-04-01',
			'openings' => 16,
			'notes' => 'Testing'
		),
		array(
			'start' => '2019-05-01',
			'openings' => 20
		),
		array(
			'start' => '2019-06-01',
			'openings' => 20,
		)
	)
));

var_dump($response);
