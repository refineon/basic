<?php
/**
 * Created by PhpStorm.
 * User: Zero
 * Date: 2020/4/3
 * Time: 10:17
 */

declare(strict_types=1);

namespace Refineon\Basic\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TokenExistMiddleware implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (token()) {
            return $handler->handle($request);
        } else {
            return response()->json([
                'code' => 403,
                'msg' => 'token不存在'
            ]);
        }
    }
}
