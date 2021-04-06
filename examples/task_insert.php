<?php

// set directory
chdir(__DIR__);
require 'init.i.php';

// create a new task and fill in details
$task = new \Arctic\Model\Task\Task();
$task->assignedagentid = 2;
$task->notes = str_repeat('test ', 25);
$task->dueon = '2021-04-30';
$task->tripid = 831;


// insert the task
$task->insert();

