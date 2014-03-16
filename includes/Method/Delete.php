<?php

namespace Arctic\Method;

use Arctic\Api;

class Delete extends Method
{
	public function __construct() {
		parent::__construct( self::TYPE_EXISTING_MODEL , Api::METHOD_DELETE );
	}
}
