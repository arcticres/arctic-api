<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// save arctic inquiry
// create a new person and fill in details (name)
$person = new \Arctic\Model\Person\Person();
$person->namefirst = $_POST['namefirst'];
$person->namelast = $_POST['namelast'];
$person->customersource = $_POST['hear'];

// create an email address, and fill in details
$ea = new \Arctic\Model\Person\EmailAddress();
$ea->isprimary = true;
$ea->type = 'Home';
$ea->emailaddress = $_POST['email'];

// add the email address to the list of references
$person->emailaddresses[] = $ea;

// create an address, and fill in details
$addr = new \Arctic\Model\Person\Address();
$addr->isprimary = true;
$addr->type = 'Home';
$addr->address1 = $_POST['address'];
$addr->city = $_POST['city'];
$addr->state = $_POST['state'];
$addr->postalcode = $_POST['zip'];

// add slashes for the query
$country = addslashes($_POST['country']);
// if not the default country, run a query
$country_id = null;
if ( $country !== 'US' && $country !== 'USA' && $country !== 'United States' && $country !== 'United States of America' ) {
    // look up country id
    foreach ( \Arctic\Model\Country::query('name = \'' . $country . '\' OR twodigitcode = \'' . $country . '\' OR threedigitcode = \'' . $country . '\' LIMIT 0, 1' ) as $country ) {
        $addr->countryid = $country->id;
        $country_id = $country->id;
    }
}

// add the address to the list of references
$person->addresses[] = $addr;

if ( $_POST[ 'phone_day' ] ) {
    // create a phone number, and fill in details
    $ph = new \Arctic\Model\Person\PhoneNumber();
    $ph->isprimary = true;
    $ph->type = 'Work';
    $ph->phonenumber = $_POST[ 'phone_day' ];
    if ( $country_id ) $ph->countryid = $country_id;

    // add the phone number to the list of references
    $person->phonenumbers[] = $ph;
}

if ( $_POST[ 'phone_evening' ] ) {
    // create a phone number, and fill in details
    $ph = new \Arctic\Model\Person\PhoneNumber();
    if ( !$_POST[ 'phone_day' ] ) $ph->isprimary = true;
    $ph->type = 'Home';
    $ph->phonenumber = $_POST[ 'phone_evening' ];
    if ( $country_id ) $ph->countryid = $country_id;

    // add the phone number to the list of references
    $person->phonenumbers[] = $ph;
}

// create a note
$note = new \Arctic\Model\Person\Note();
$note->note = 'Added through contact form.';

// add the note to the list of references
$person->notes[] = $note;

// first: insert (or if similar to existing, update) the person
if ($person->insertOrUpdate()) {
	// second: create inquiry
    $inquiry = new \Arctic\Model\Inquiry\Inquiry();
    $inquiry->personid = $person->id;
    $inquiry->mode = 'Online Form';
    $inquiry->notes = $_POST[ 'message' ];

    if ( !$inquiry->insert() ) {
        // HANDLE ERROR HERE
        // if errors are silenced, can be retrieved using ArcticAPI::getLastError()
    }
}
else {
    // HANDLE ERROR HERE
    // if errors are silenced, can be retrieved using ArcticAPI::getLastError()
}

header( 'Location: contact_form_thank.php');

