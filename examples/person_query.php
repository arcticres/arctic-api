<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// run query
// - find two people, who are users, ordered by last name, then first
// queries are a very simple SQL syntax, which can handle AND/OR clauses, references, basic operators, order clauses and limits
foreach ( \Arctic\Model\Person\Person::query('isuser = TRUE ORDER BY namelast, namefirst LIMIT 0, 2' ) as $person ) {
	echo '* ' , $person->id , ': ' , $person->namefirst , ' ' , $person->namelast , "\n";
}
