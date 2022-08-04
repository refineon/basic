<?php

/**
 * Created by PhpStorm.
 * User: zero
 * Date: 2020/2/11
 * Time: 11:49
 */

namespace Refineon\Basic;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'exceptions' => [
                /**
                 * 以下异常处理器会合并到项目的config/autoload/exceptions.php文件配置数组的前面;
                 * 请勿在此使用顶级异常捕获处理器,防止项目中异常处理器无效;
                 */
                'handler' => [
                    'http' => [
                        \Refineon\Basic\Exceptions\Handler\MicroExceptionHandler::class,
                        \Refineon\Basic\Exceptions\Handler\QueryExceptionHandler::class,
                        \Refineon\Basic\Exceptions\Handler\PhpSpreadsheetExceptionHandler::class,
                    ],
                ],
            ],
            'dependencies' => [
                \Hyperf\ServiceGovernance\Listener\RegisterServiceListener::class => \Meibuyu\Micro\Listener\RegisterServiceListener::class,
            ],
            'commands' => [
                \Refineon\Basic\Command\MakeModelCommand::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
        ];
    }
}
