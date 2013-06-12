<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// write person
$person = new Arctic_Person();
$person->namefirst = 'Hannah';
$person->namelast = 'Wyoming';

$ea = new Arctic_Person_EmailAddress();
$ea->isprimary = true;
$ea->type = 'Home';
$ea->emailaddress = 'hannah@maxmo.net';

$person->emailaddresses[] = $ea;

$person->insert();

