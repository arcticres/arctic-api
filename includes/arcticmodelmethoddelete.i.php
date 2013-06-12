<?php

class ArcticModelMethodDelete extends ArcticModelMethod
{
	public function __construct() {
		parent::__construct( self::TYPE_EXISTING_MODEL , ArcticAPI::METHOD_DELETE );
	}
}