<?php

declare(strict_types=1);

namespace Refineon\Basic\Exceptions\Handler;

use Exception;
use Hyperf\Amqp\Producer;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var StdoutLoggerInterface
     */
    protected $stdoutLogger;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var RequestInterface
     */
    protected $request;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->stdoutLogger = $container->get(StdoutLoggerInterface::class);
        $this->logger = $container->get(LoggerFactory::class)->get('Uncaught Exception');
        $this->config = $container->get(ConfigInterface::class);
        $this->request = $container->get(RequestInterface::class);
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // 捕获所有未捕获的异常
        $this->stopPropagation();
        $api = sprintf('%s(%s)', $this->request->getUri(), $this->request->getMethod());
        $exceptionClass = get_class($throwable);
        $message = $throwable->getMessage();
        $line = $throwable->getLine();
        $file = $throwable->getFile();
        $code = $throwable->getCode();
        $trace = $throwable->getTraceAsString();
//        $data = [
//            'api' => $api,
//            'server' => $this->config->get('app_name'),
//            'file' => $file,
//            'line' => $line,
//            'message' => $message,
//            'trace' => $trace,
//            'code' => $code,
//            'created_at' => now(),
//        ];
//        try {
//            $exceptionLogProducer = new ExceptionLogProducer($data);
//            $producer = $this->container->get(Producer::class);
//            $producer->produce($exceptionLogProducer);
//        } catch (Exception $e) {
//            put_log('异常日志失败; ' . $e->getMessage(), 'ExceptionLogProducer.log');
//        }
        $msg = sprintf('%s: %s(%s) in %s:%s', $exceptionClass, $message, $code, $file, $line);
        $error = sprintf("API: %s\n%s\nStack trace:\n%s", $api, $msg, $trace);
        $this->logger->error($error);
        $this->stdoutLogger->error($error);
        return $response->withStatus(500)->withBody(new SwooleStream($msg));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
