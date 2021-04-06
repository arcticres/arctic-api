<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// create a new inquiry and fill in details
$inquiry = new \Arctic\Model\Inquiry\Inquiry();
$inquiry->personid = 199;
$inquiry->notes = str_repeat('test ', 23110);

// insert the inquiry
$inquiry->insert();
