<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// load person with ID #7 (assumes they exist)
$person = \Arctic\Model\Person\Person::load(7);
echo $person->namefirst , ' ' , $person->namelast , "\n";

// print email addresses
echo 'EMAIL ADDRESSES' , "\n";
foreach ( $person->emailaddresses  as $ea ) {
	echo '* ' , $ea->type , ': ' , $ea->emailaddress , "\n";
}

// print addresses
echo 'ADDRESSES' , "\n";
foreach ( $person->addresses  as $ea ) {
	echo '* ' , $ea->type , ': ' , "\n";
	if ( $ea->address1 ) echo "\t$ea->address1\n";
	if ( $ea->address2 ) echo "\t$ea->address2\n";
	echo "\t$ea->city $ea->state, $ea->postalcode\n";
	echo "\t{$ea->country->name}\n";
}

// print phone numbers
echo 'PHONE NUMBERS' , "\n";
foreach ( $person->phonenumbers  as $ea ) {
	echo '* ' , $ea->type , ': ' , $ea->phonenumber , "\n";
}

// print notes
echo 'NOTES' , "\n";
foreach ( $person->notes  as $ea ) {
	echo '* ' , $ea->note , "\n";
}
echo "\n\n";
