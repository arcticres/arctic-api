<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

$person = Arctic_Person::load(3);
echo $person->namefirst , ' ' , $person->namelast , "\n";

// add it
$ea = new Arctic_Person_EmailAddress();
$ea->type = 'Home';
$ea->emailaddress = 'ronda@maxmo.net';

// inserted simply be setting
$person->emailaddresses[] = $ea;
