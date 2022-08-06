<?php

namespace Refineon\Basic\Exceptions\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class PhpSpreadsheetExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $error = $throwable->getMessage();
        if ($throwable instanceof PhpSpreadsheetException) {
            if (strpos($error, 'Formula Error') !== false) {
                $msg = '表格公式错误, 请检查是否引用其它表格数据';
                return $this->jsonResponse($msg, $response);
            }
        } else if (strpos($error, 'PhpOffice\PhpSpreadsheet\Writer\Xls::writeSummaryProp()') !== false) {
            $msg = '表格格式兼容错误,请上传 xlsx 结尾的excel';
            return $this->jsonResponse($msg, $response);
        }
        return $response;
    }

    public function jsonResponse($msg, ResponseInterface $response)
    {
        // 阻止异常冒泡
        $this->stopPropagation();
        // 格式化输出
        $data = json_encode([
            'code' => 400,
            'msg' => $msg,
        ], JSON_UNESCAPED_UNICODE);
        return $response->withAddedHeader('content-type', 'application/json')->withBody(new SwooleStream($data));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }

}
