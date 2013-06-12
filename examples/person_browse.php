<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// run browse
foreach ( Arctic_Person::browse(0,5) as $person ) {
	echo '* ' , $person->id , ': ' , $person->namefirst , ' ' , $person->namelast , "\n";
	// check references
	foreach ( $person->emailaddresses  as $ea ) {
		echo "\t" , '* ' , $ea->type , ': ' , $ea->emailaddress , "\n";
	}
}