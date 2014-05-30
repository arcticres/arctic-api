<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

$trips = \Arctic\Model\Trip\Trip::query('start.daterelative APPLY("count", 3, "direction", "past", "operator", "on-or-after") AND start.daterelative APPLY("operator", "on-or-before") AND guests >= 1');
foreach ($trips as $trip) {
    echo '* ', $trip->name, ' ', $trip->start, ' ', $trip->starttime, PHP_EOL;
}
