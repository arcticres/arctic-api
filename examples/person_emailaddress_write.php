<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// loads person with ID #3 (assumes they exist)
$person = \Arctic\Model\Person\Person::load(3);
echo $person->namefirst , ' ' , $person->namelast , "\n";

// change the type of the first email address (again, assumes it exists)
$person->emailaddresses[0]->type = 'Work!';
$person->emailaddresses[0]->update();
