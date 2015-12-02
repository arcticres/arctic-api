<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// load invoice by ID (if invoice number is P185, the ID is 185)
try {
	$invoice = \Arctic\Model\Invoice\Invoice::load(185);
}
catch (\Arctic\Exception $e) {
	// invoice not found or communication error
	die('Unable to load invoice.');
}

// create transaction
$transaction = new \Arctic\Model\Invoice\Transaction();
$transaction->type = \Arctic\Model\Invoice\Transaction::TYPE_PAYMENT;
$transaction->description = 'Online Payment';
$transaction->amount = '100.00'; // amount of payment
$transaction->time = date('Y-m-d H:i:s'); // current date and time

// inserted simply by adding it to the reference array, saved upon insertion into the array
$invoice->transactions[] = $transaction;

// refresh invoice balance due and payment details
$invoice->refresh();
