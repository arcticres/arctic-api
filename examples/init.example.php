<?php

// FOR THE EXAMPLES TO WORK, RENAME THIS TO init.i.php AND ADD THE REQUIRED INFORMATION

// load Arctic API
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'arctic.i.php';

// initiate Arctic API
ArcticAPI::init('INSTALLATION_NAME','API_USERNAME','API_PASSWORD',array(
	'client_id'		=>	'CLIENT_ID',
	'client_secret'	=>	'CLIENT_SECRET'
));

