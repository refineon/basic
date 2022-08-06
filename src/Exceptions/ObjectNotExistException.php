<?php

namespace Refineon\Basic\Exceptions;

use Throwable;

class ObjectNotExistException extends \Exception
{
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message . ' Not Exist!', $code, $previous);
    }
}