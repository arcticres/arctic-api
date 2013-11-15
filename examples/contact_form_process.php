<?php


// save arctic inquiry
// create a new person and fill in details (name)
$person = new Arctic_Person();
$person->namefirst = $_POST['namefirst'];
$person->namelast = $_POST['namefirst'];
$person->customersource = $_POST['hear'];

// create an email address, and fill in details
$ea = new Arctic_Person_EmailAddress();
$ea->isprimary = true;
$ea->type = 'Home';
$ea->emailaddress = $_POST['email'];

// add the email address to the list of references
$person->emailaddresses[] = $ea;

// create an address, and fill in details
$addr = new Arctic_Person_Address();
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
    foreach ( Arctic_Country::query('name = \'' . $country . '\' OR twodigitcode = \'' . $country . '\' OR threedigitcode = \'' . $country . '\' LIMIT 0, 1' ) as $country ) {
        $addr->countryid = $country->id;
        $country_id = $country->id;
    }
}

// add the address to the list of references
$person->addresses[] = $addr;

if ( $_POST[ 'phone_day' ] ) {
    // create a phone number, and fill in details
    $ph = new Arctic_Person_PhoneNumber();
    $ph->isprimary = true;
    $ph->type = 'Work';
    $ph->phonenumber = $_POST[ 'phone_day' ];
    if ( $country_id ) $ph->countryid = $country_id;

    // add the phone number to the list of references
    $person->phonenumbers[] = $ph;
}

if ( $_POST[ 'phone_evening' ] ) {
    // create a phone number, and fill in details
    $ph = new Arctic_Person_PhoneNumber();
    if ( !$_POST[ 'phone_day' ] ) $ph->isprimary = true;
    $ph->type = 'Home';
    $ph->phonenumber = $_POST[ 'phone_evening' ];
    if ( $country_id ) $ph->countryid = $country_id;

    // add the phone number to the list of references
    $person->phonenumbers[] = $ph;
}

// create a note
$note = new Arctic_Person_Note();
$note->note = 'Added through contact form.';

// add the note to the list of references
$person->notes[] = $note;

// insert both the person and any new references
if ( $person->insert() ) {
    $inquiry = new Arctic_Inquiry();
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

