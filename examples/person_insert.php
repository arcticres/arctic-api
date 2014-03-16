<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// create a new person and fill in details (name)
$person = new \Arctic\Model\Person\Person();
$person->namefirst = 'Hannah';
$person->namelast = 'Wyoming';

// create an email address, and fill in details
$ea = new \Arctic\Model\Person\EmailAddress();
$ea->isprimary = true;
$ea->type = 'Home';
$ea->emailaddress = 'hannah@maxmo.net';

// add the email address to the list of references
$person->emailaddresses[] = $ea;

// insert both the person and any new references
$person->insert();
