<?php

namespace Refineon\Basic\Exceptions;

use Throwable;

class RpcException extends \Exception
{

    public function __construct($message = "", $code = 511, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
