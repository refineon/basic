<?php

namespace Meibuyu\Micro\Exceptions\Handler;


use Hyperf\Database\Exception\QueryException;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class QueryExceptionHandler extends ExceptionHandler
{

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $code = $throwable->getCode();
        $msg = $throwable->getMessage();
        if ($code == 23000 && strpos($msg, 'foreign key') !== false) {
            // 格式化输出
            $data = json_encode([
                'code' => 400,
                'msg' => '此数据下有关联的数据,不可进行操作',
            ], JSON_UNESCAPED_UNICODE);
            // 阻止异常冒泡
            $this->stopPropagation();
            return $response
                ->withAddedHeader('content-type', 'application/json')
                ->withBody(new SwooleStream($data));
        }
        return $response; // 交给下一个异常处理器
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof QueryException;
    }

}
