<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// run query
foreach ( Arctic_Person::query('isuser = TRUE ORDER BY namelast, namefirst LIMIT 0, 2' ) as $person ) {
	echo '* ' , $person->id , ': ' , $person->namefirst , ' ' , $person->namelast , "\n";
}