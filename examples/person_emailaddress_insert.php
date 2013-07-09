<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// loads person with ID #3 (assumes they exist)
$person = Arctic_Person::load(3);
echo $person->namefirst , ' ' , $person->namelast , "\n";

// create a new email address, and populate the fields
$ea = new Arctic_Person_EmailAddress();
$ea->type = 'Home';
$ea->emailaddress = 'ronda@maxmo.net';

// inserted simply by adding it to the reference array, saved upon insertion into the array
$person->emailaddresses[] = $ea;
