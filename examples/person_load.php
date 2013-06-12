<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// run load
$person = Arctic_Person::load(7);
echo $person->namefirst , ' ' , $person->namelast , "\n";

echo 'EMAIL ADDRESSES' , "\n";
// check references
foreach ( $person->emailaddresses  as $ea ) {
	echo '* ' , $ea->type , ': ' , $ea->emailaddress , "\n";
}

echo 'ADDRESSES' , "\n";
// check references
foreach ( $person->addresses  as $ea ) {
	echo '* ' , $ea->type , ': ' , "\n";
	if ( $ea->address1 ) echo "\t$ea->address1\n";
	if ( $ea->address2 ) echo "\t$ea->address2\n";
	echo "\t$ea->city $ea->state, $ea->postalcode\n";
	echo "\t{$ea->country->name}\n";
}

echo 'PHONE NUMBERS' , "\n";
// check references
foreach ( $person->phonenumbers  as $ea ) {
	echo '* ' , $ea->type , ': ' , $ea->phonenumber , "\n";
}

echo 'NOTES' , "\n";
// check references
foreach ( $person->notes  as $ea ) {
	echo '* ' , $ea->note , "\n";
}
echo "\n\n";
