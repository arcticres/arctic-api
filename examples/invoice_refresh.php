<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// load invoice
$invoice = \Arctic\Model\Invoice\Invoice::load(185);

// total cost
echo "Total Cost: ", $invoice->totalcost, PHP_EOL;

// change amount of first subitem
$item = $invoice->groups[0]->items[0]->subitems[0];
echo "Change ", $item->shortname, " amount from " , $item->amount;
$item->amount += 0.25;
echo " to ", $item->amount , PHP_EOL;
$item->update();

// refresh invoice
if ( $invoice->refresh() ) {
    echo "New Total Cost: ", $invoice->totalcost, PHP_EOL;
}
