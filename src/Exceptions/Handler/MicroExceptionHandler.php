<?php

namespace Refineon\Basic\Exceptions\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Refineon\Basic\Exceptions\HttpResponseException;
use Refineon\Basic\Exceptions\ValidatorException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class MicroExceptionHandler extends ExceptionHandler
{

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // 判断被捕获到的异常是希望被捕获的异常
        if ($throwable instanceof HttpResponseException) {
            // 格式化输出
            $data = json_encode([
                'code' => $throwable->getCode() ?: 400,
                'msg' => $throwable->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
            // 阻止异常冒泡
            $this->stopPropagation();
            return $response
                ->withAddedHeader('content-type', 'application/json')
                ->withBody(new SwooleStream($data));
        } else if ($throwable instanceof ValidatorException) {
            $this->stopPropagation(); // 阻止异常冒泡
            /** @var ValidatorException $throwable */
            $data = json_encode([
                'code' => $throwable->getCode() ?: 401,
                'msg' => $throwable->first(),
            ], JSON_UNESCAPED_UNICODE);

            return $response
                ->withAddedHeader('content-type', 'application/json')
                ->withBody(new SwooleStream($data));
        }
        return $response; // 交给下一个异常处理器
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }

}
