<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// run browse
//  gets the first 5 people in the database
//  0   is the starting index
//  5   is the number of entries to return
foreach ( \Arctic\Model\Person\Person::browse(0,5) as $person ) {
	echo '* ' , $person->id , ': ' , $person->namefirst , ' ' , $person->namelast , "\n";
	// check references
	foreach ( $person->emailaddresses  as $ea ) {
		echo "\t" , '* ' , $ea->type , ': ' , $ea->emailaddress , "\n";
	}
}
