<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// run browse
//  gets the first 5 invoices in the database
//  0   is the starting index
//  5   is the number of entries to return
foreach ( \Arctic\Model\Invoice\Invoice::browse(0,5) as $invoice ) {
    echo '* ' , $invoice->id , ': ' , $invoice->person->namefirst , ' ' , $invoice->person->namelast , "\n";
    // check references
    foreach ( $invoice->groups  as $grp ) {
        echo "\t" , '* ' , $grp->name , "\n";
        foreach ( $grp->items as $itm ) {
            echo "\t\t" , '* ' , $itm->description , " (", $itm->calculatedamount, ")\n";
            foreach ( $itm->subitems as $sub ) {
                echo "\t\t\t" , '* ' , $sub->description , " (", $sub->calculatedamount, ")\n";
            }
        }
    }
    foreach ( $invoice->items as $itm ) {
        echo "\t\t" , '* ' , $itm->description , " (", $itm->calculatedamount, ")\n";
        foreach ( $itm->subitems as $sub ) {
            echo "\t\t\t" , '* ' , $sub->description , " (", $sub->calculatedamount, ")\n";
        }
    }
}
