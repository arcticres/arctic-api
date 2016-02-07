<?php

namespace Arctic;

class Exception extends \Exception
{
	const TYPE_PARSE = -2;
	const TYPE_CONNECTION = -1;
	const TYPE_BAD_REQUEST = 400;
	const TYPE_UNAUTHORIZED = 401;
	const TYPE_FORBIDDEN = 403;
	const TYPE_NOT_FOUND = 404;
	const TYPE_SERVER = 500;

	public static function create($type, $message) {
		switch ($type) {
			case self::TYPE_PARSE:
				return new Exception\Parse($message);
			case self::TYPE_CONNECTION:
				return new Exception\Connection($message);
			case self::TYPE_BAD_REQUEST:
				return new Exception\BadRequest($message);
			case self::TYPE_UNAUTHORIZED:
				return new Exception\Unauthorized($message);
			case self::TYPE_FORBIDDEN:
				return new Exception\Forbidden($message);
			case self::TYPE_NOT_FOUND:
				return new Exception\NotFound($message);
			case self::TYPE_SERVER:
				return new Exception\Server($message);
			default:
				return new self($message);
		}
	}
}
