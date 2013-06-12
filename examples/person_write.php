<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// write person
$person = Arctic_Person::load(3);
$person->namecompany = 'Self-Employed';
$person->update();