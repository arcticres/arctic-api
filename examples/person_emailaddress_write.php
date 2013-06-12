<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

$person = Arctic_Person::load(3);
echo $person->namefirst , ' ' , $person->namelast , "\n";
$person->emailaddresses[0]->type = 'Work!';
$person->emailaddresses[0]->update();
