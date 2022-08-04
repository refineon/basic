<?php
/**
 * Created by PhpStorm.
 * User: 梁俊杰
 * Date: 2019/12/03
 * Time: 16:08
 * Description:
 */

namespace Refineon\Basic;

class Helper
{
    /**
     * 获取唯一编号
     * @param string $prefix
     * @return string
     */
    public static function uid($prefix = '')
    {
        return uniqid($prefix);
    }

    /**
     * 返回成功消息
     * @param array|string $data 返回数据 默认空
     * @param string $msg 消息，默认 Success
     * @param int $code 成功代码，默认200
     * @return array
     */
    public static function success($data = '', $msg = 'success', $code = 200)
    {
        return self::response($data, $msg, $code);
    }

    /**
     * 返回失败消息
     * @param array|string $data 返回数据 默认空
     * @param string $msg 消息，默认 Error
     * @param int $code 失败代码，默认400
     * @return array
     */
    public static function fail($data = '', $msg = 'fail', $code = 400)
    {
        return self::response($data, $msg, $code);
    }

    /**
     * 返回操作消息
     * @param array|string $data 返回数据 默认空
     * @param string $msg 消息，默认空
     * @param int $code 操作代码，默认200
     * @return array
     */
    public static function response($data = '', $msg = '', $code = 200)
    {
        return ['data' => $data, 'msg' => $msg, 'code' => $code];
    }
}
