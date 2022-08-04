<?php

namespace Refineon\Basic\Model;

use Hyperf\Utils\Context;
use Refineon\Basic\Exceptions\HttpResponseException;

class Auth
{

    /**
     * @return bool|mixed|string|null
     * @throws HttpResponseException
     */
    private static function init()
    {
        if (Context::has('auth')) {
            return Context::get('auth');
        } else {
            $token = token();
            if (!$token) throw new HttpResponseException('Token不存在');
            $auth = redis()->get($token);
            if ($auth) {
                $auth = json_decode($auth, true);
                Context::set('auth', $auth);
                return $auth;
            } else {
                throw new HttpResponseException('用户不存在');
            }
        }
    }

    /**
     * @return object
     * @throws HttpResponseException
     */
    public static function user()
    {
        return self::init();
    }

    /**
     * @return integer
     * @throws HttpResponseException
     */
    public static function id()
    {
        return self::init()['id'];
    }

}
