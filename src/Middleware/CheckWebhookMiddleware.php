<?php
/**
 * Created by PhpStorm.
 * User: Zero
 * Date: 2020/8/24
 * Time: 9:33
 */

namespace Refineon\Basic\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CheckWebhookMiddleware implements MiddlewareInterface
{

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $domain = $request->getHeader('x-shopify-shop-domain')[0] ?? null;
        $topic = $request->getHeader('x-shopify-topic')[0] ?? null;
        if ($domain && $topic) {
            return $handler->handle($request);
        } else {
            return response()->withStatus(500);
        }
    }

}
