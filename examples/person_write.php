<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// load person #3
$person = \Arctic\Model\Person\Person::load(3);

// change their information
$person->namecompany = 'Self-Employed';

// issue update command
$person->update();
