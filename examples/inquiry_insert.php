<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// create a new person and fill in details (name)
$inquiry = new \Arctic\Model\Inquiry\Inquiry();
$inquiry->personid = 199;
$inquiry->notes = str_repeat('test ', 23110);

// insert both the inquiry
$inquiry->insert();
