<?php

namespace Arctic\Method;

use Arctic\Api;

class Delete extends Method
{
	public function __construct() {
		parent::__construct( self::TYPE_EXISTING_MODEL , Api::METHOD_DELETE );
	}

    protected function _prepareRequest( $api_path , $arguments ) {
        // update cache if there is an ID
        if ($id = $this->_model->getID()) {
            Api::getInstance()->getCacheManager()->remove($id, $this->_model_class);
        }

        return parent::_prepareRequest($api_path, $arguments);
    }
}
