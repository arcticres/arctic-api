<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// run browse
//  gets the first 5 responses in the database
//  0   is the starting index
//  5   is the number of entries to return
foreach (\Arctic\Model\Evaluation\Response::browse(0,5) as $response ) {
	echo '* ' , $response->id , ': ' , $response->personid , ' ' , $response->useragent , "\n";
}
