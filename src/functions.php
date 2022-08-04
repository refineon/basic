<?php

use Hyperf\Cache\Listener\DeleteListenerEvent;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Logger\Logger;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * 容器实例
 */
if (!function_exists('container')) {
    function container($key = null)
    {
        if (is_null($key)) {
            return ApplicationContext::getContainer();
        } else {
            return ApplicationContext::getContainer()->get($key);
        }
    }
}

if (!function_exists('redis')) {
    /**
     * 获取redis客户端实例
     * @return Redis|mixed
     */
    function redis()
    {
        return container(Redis::class);
    }
}

/**
 * token
 */
if (!function_exists('token')) {
    function token()
    {
        $token = request()->getHeader('Authorization')[0] ?? '';
        if (strlen($token) > 0) {
            $token = ucfirst($token);
            $arr = explode('Bearer ', $token);
            $token = $arr[1] ?? '';
            if (strlen($token) > 0) {
                return $token;
            }
        }
        return false;
    }
}

if (!function_exists('request')) {
    /**
     * 请求实例
     * @param array|string|null $key
     * @param mixed $default
     * @return RequestInterface|string|array|mixed
     */
    function request($key = null, $default = null)
    {
        if (is_null($key)) {
            return container(RequestInterface::class);
        }

        if (is_array($key)) {
            return container(RequestInterface::class)->inputs($key);
        }

        $value = container(RequestInterface::class)->input($key);

        return is_null($value) ? value($default) : $value;
    }
}

if (!function_exists('response')) {
    /**
     * 响应实例
     * @return mixed|ResponseInterface
     */
    function response()
    {
        return container(ResponseInterface::class);
    }
}

if (!function_exists('success')) {
    /**
     * 成功响应实例
     * @param string $msg
     * @param mixed $data
     * @param int $code
     * @return mixed
     */
    function success($msg = '', $data = null, $code = 200)
    {
        return response()->json([
            'msg' => $msg,
            'data' => $data,
            'code' => $code
        ]);
    }
}

if (!function_exists('fail')) {
    /**
     * 失败响应实例
     * @param string $msg
     * @param mixed $data
     * @param int $code
     * @return mixed
     */
    function fail($msg = '', $data = null, $code = 400)
    {
        return response()->json([
            'msg' => $msg,
            'data' => $data,
            'code' => $code
        ]);
    }
}

if (!function_exists('download')) {
    /**
     * 文件流下载文件
     * @param string $filePath 文件路径
     * @param string $showName 下载后展示的名称
     * @return mixed
     */
    function download($filePath = '', $showName = '')
    {
        return response()->download($filePath, urlencode($showName));
    }
}

if (!function_exists('decimal_to_abc')) {
    /**
     * 数字转换对应26个字母
     * @param $num
     * @return string
     * @deprecated 此方法废弃,请使用int_to_chr()
     */
    function decimal_to_abc($num)
    {
        $str = "";
        $ten = $num;
        if ($ten == 0) return "A";
        while ($ten != 0) {
            $x = $ten % 26;
            $str .= chr(65 + $x);
            $ten = intval($ten / 26);
        }
        return strrev($str);
    }
}

if (!function_exists('diff_between_two_days')) {
    /**
     * 计算两个日期之间相差的天数
     * @param $day1
     * @param $day2
     * @return float|int
     */
    function diff_between_two_days($day1, $day2)
    {
        $second1 = strtotime($day1);
        $second2 = strtotime($day2);
        return round((abs($second1 - $second2) / 86400), 0);
    }
}

if (!function_exists('decimals_to_percentage')) {
    /**
     * 将小数转换百分数
     * @param float $decimals 小数
     * @param int $num 保留小数位
     * @return string
     */
    function decimals_to_percentage($decimals, $num = 2)
    {
        return sprintf("%01." . $num . "f", $decimals * 100) . '%';
    }
}

if (!function_exists('calculate_grade')) {
    /**
     *
     * 计算一个数的区间范围等级
     * @param array $range 区间范围（从大到小排列）
     * @param $num
     * @return mixed|void
     */
    function calculate_grade($range, $num)
    {
        $max = max($range);
        if ($num >= $max) {
            return count($range);
        }
        foreach ($range as $k => $v) {
            if ($num < $v) {
                return $k;
            }
        }
    }
}

if (!function_exists('convertAmountToCn')) {
    /**
     * 2  * 将数值金额转换为中文大写金额
     * 3  * @param $amount float 金额(支持到分)
     * 4  * @param $type   int   补整类型,0:到角补整;1:到元补整
     * 5  * @return mixed 中文大写金额
     * 6  */
    function convertAmountToCn($amount, $type = 1)
    {
        // 判断输出的金额是否为数字或数字字符串
        if (!is_numeric($amount)) {
            return "要转换的金额只能为数字!";
        }

        // 金额为0,则直接输出"零元整"
        if ($amount == 0) {
            return "人民币零元整";
        }

        // 金额不能为负数
        if ($amount < 0) {
            return "要转换的金额不能为负数!";
        }

        // 金额不能超过万亿,即12位
        if (strlen($amount) > 12) {
            return "要转换的金额不能为万亿及更高金额!";
        }

        // 预定义中文转换的数组
        $digital = array('零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖');
        // 预定义单位转换的数组
        $position = array('仟', '佰', '拾', '亿', '仟', '佰', '拾', '万', '仟', '佰', '拾', '元');

        // 将金额的数值字符串拆分成数组
        $amountArr = explode('.', $amount);

        // 将整数位的数值字符串拆分成数组
        $integerArr = str_split($amountArr[0], 1);

        // 将整数部分替换成大写汉字
        $result = '人民币';
        $integerArrLength = count($integerArr);     // 整数位数组的长度
        $positionLength = count($position);         // 单位数组的长度
        for ($i = 0; $i < $integerArrLength; $i++) {
            // 如果数值不为0,则正常转换
            if ($integerArr[$i] != 0) {
                $result = $result . $digital[$integerArr[$i]] . $position[$positionLength - $integerArrLength + $i];
            } else {
                // 如果数值为0, 且单位是亿,万,元这三个的时候,则直接显示单位
                if (($positionLength - $integerArrLength + $i + 1) % 4 == 0) {
                    $result = $result . $position[$positionLength - $integerArrLength + $i];
                }
            }
        }

        // 如果小数位也要转换
        if ($type == 0) {
            // 将小数位的数值字符串拆分成数组
            $decimalArr = str_split($amountArr[1], 1);
            // 将角替换成大写汉字. 如果为0,则不替换
            if ($decimalArr[0] != 0) {
                $result = $result . $digital[$decimalArr[0]] . '角';
            }
            // 将分替换成大写汉字. 如果为0,则不替换
            if ($decimalArr[1] != 0) {
                $result = $result . $digital[$decimalArr[1]] . '分';
            }
        } else {
            $result = $result . '整';
        }
        return $result;
    }
}

if (!function_exists('today')) {
    /**
     * Create a new Carbon instance for the current time.
     * @return false|string
     */
    function today()
    {
        return date('Y-m-d', time());
    }
}

if (!function_exists('now')) {
    /**
     * Create a new Carbon instance for the current time.
     * @return false|string
     */
    function now()
    {
        return date('Y-m-d H:i:s', time());
    }
}

if (!function_exists('get_tree_id')) {
    /**
     * @param array $array
     * @param array $pid
     * @return array
     */
    function get_tree_id(array $array, $pids = [0])
    {
        $list = [];
        foreach ($array as $key => $value) {
            if (in_array($value['pid'], $pids) || in_array($value['id'], $pids)) {
                $list[] = $value['id'];
                unset($array[$key]);
            }
        }
        if ($list == []) return [];
        $children = get_tree_id($array, $list);
        return array_merge($list, $children);
    }
}

if (!function_exists('list_go_tree')) {
    /**
     * 列表转树状格式
     * @param array $list
     * @param string $pk
     * @param string $pid
     * @param string $children
     * @return array
     */
    function list_go_tree(array $list = [], $pk = 'id', $pid = 'parent_id', $children = 'children')
    {
        $tree = $refer = [];
        // 创建基于主键的数组引用
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] = &$list[$key];
        }
        foreach ($list as $key => $data) {
            $parentId = $data[$pid];
            // 判断是否存在parent
            if (isset($refer[$parentId])) {
                $parent = &$refer[$parentId];
                $parent[$children][] = &$list[$key];
            } else {
                $tree[] = &$list[$key];
            }
        }
        return $tree;
    }
}

if (!function_exists('flushAnnotationCache')) {
    /**
     * 刷新注解缓存，清楚注解缓存
     * @param string $listener
     * @param mixed $keys
     * @return bool
     */
    function flushAnnotationCache($listener, $keys)
    {
        $keys = is_array($keys) ? $keys : [$keys];
        $dispatcher = ApplicationContext::getContainer()->get(EventDispatcherInterface::class);
        foreach ($keys as $key) {
            $dispatcher->dispatch(new DeleteListenerEvent($listener, [$key]));
        }
        return true;
    }
}

if (!function_exists('num_2_file_size')) {
    /**
     * 数字转文件大小
     * @param $num
     * @return string
     */
    function num_2_file_size($num)
    {
        $p = 0;
        $format = 'B';
        if ($num > 0 && $num < 1024) {
            return number_format($num) . ' ' . $format;
        } else if ($num >= 1024 && $num < pow(1024, 2)) {
            $p = 1;
            $format = 'KB';
        } else if ($num >= pow(1024, 2) && $num < pow(1024, 3)) {
            $p = 2;
            $format = 'MB';
        } else if ($num >= pow(1024, 3) && $num < pow(1024, 4)) {
            $p = 3;
            $format = 'GB';
        } else if ($num >= pow(1024, 4) && $num < pow(1024, 5)) {
            $p = 4;
            $format = 'TB';
        }
        $num /= pow(1024, $p);
        return number_format($num, 2) . ' ' . $format;
    }
}

if (!function_exists('select_id_name')) {
    function select_id_name($columns = [])
    {
        $columns = array_merge(['id', 'name'], $columns);
        return function ($q) use ($columns) {
            $q->select($columns);
        };
    }
}

if (!function_exists('get_week_start_and_end')) {
    function get_week_start_and_end($time = '', $first = 1)
    {
        //当前日期
        if (!$time) $time = time();
        $sdefaultDate = date("Y-m-d", $time);
        //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
        //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
        $w = date('w', strtotime($sdefaultDate));
        //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
        $week_start = date('Y-m-d', strtotime("$sdefaultDate -" . ($w ? $w - $first : 6) . ' days'));
        //本周结束日期
        $week_end = date('Y-m-d', strtotime("$week_start +6 days"));
        return array("week_start" => $week_start, "week_end" => $week_end);
    }
}

if (!function_exists('putLog')) {
    /**
     * description:记录日志 文件会生成在当前项目 /runtime/dev/
     * author: fuyunnan
     * @param string|array $output 日志 内容
     * @param string $dir 目录
     * @param string $filename 文件名称
     * date: 2020/3/18
     * @return void
     * @throws
     */
    function put_log($output = 'out-mes', $filename = '', $dir = BASE_PATH . '/runtime/logs/dev/')
    {
        !is_dir($dir) && !mkdir($dir, 0777, true);
        // 创建一个 Channel，参数 log 即为 Channel 的名字
        $log = make(Logger::class, ['']);
        // 创建两个 Handler，对应变量 $stream 和 $fire
        !$filename && $filename = date('Y-m-d', time()) . '.log';
        $stream = make(StreamHandler::class, [$dir . $filename, Logger::WARNING]);
        $fire = make(FirePHPHandler::class);
        if (is_array($output)) {
            $output = var_export($output, true);
        }
        $output = '[ ' . date('Y-m-d H:i:s', time()) . ' ] --- ' . $output;
        $formatter = new LineFormatter($output . "\r\n");
        $stream->setFormatter($formatter);
        $log->pushHandler($stream);
        $log->pushHandler($fire);
        $log->alert('');
    }
}

if (!function_exists('http_to_server_url')) {
    /**
     * description:将前端的绝对路径转化为服务端相对路径
     * author: fuyunnan
     * @param string $path 需要转化的路径
     * @return string
     * @throws
     * Date: 2020/6/24
     */
    function http_to_server_url($path)
    {
        $path = ltrim(parse_url($path, PHP_URL_PATH), '/');
        return 'public/' . $path;
    }
}

if (!function_exists('empty_string_2_null')) {
    /**
     * 空字符串转NULL
     * @param array $arr
     * @return array
     */
    function empty_string_2_null(array $arr)
    {
        if (!empty($arr)) {
            foreach ($arr as $key => $value) {
                if (is_array($value)) {
                    $arr[$key] = empty_string_2_null($value);
                } else {
                    if ($value === '') {
                        $arr[$key] = null;
                    }
                }
            }
        }
        return $arr;
    }
}

if (!function_exists('get_collection_values')) {
    /**
     * 从集合中提取深层数据
     * @param mixed $collection 数据集合
     * @param string $key 集合中键 支持多层提取，例如"a.b.c"
     * @return array | collection
     */
    function get_collection_values($collection, string $key)
    {
        $values = [];
        if (!empty($collection) && (is_object($collection) || is_array($collection))) {
            $itemKeys = explode(".", $key);
            $keyCount = count($itemKeys) - 1;
            foreach ($collection as $value) {
                foreach ($itemKeys as $k => $ik) {
                    if (isset($value[$ik])) {
                        $value = $value[$ik];
                        if ($k == $keyCount) {
                            $values[] = $value;
                        }
                    } else {
                        if (is_array($value) || is_countable($collection)) {
                            foreach ($value as $vv) {
                                if (isset($vv[$ik])) {
                                    $values[] = $vv[$ik];
                                }
                            }
                        }
                        break;
                    }
                }
            }
        }
        return $values;
    }
}

if (!function_exists('put_collection_values')) {
    /**
     * 对集合中设置深层数据
     * @param array | collection $collection 需要设置的数据集合
     * @param array | collection $valueList 需要设置值集合
     * @param string $collectionKey 集合中键 支持多层提取，例如"user.info"
     * @param string $collectionNewKey 数据集合的新键，例如"infomation"，同名会覆盖
     * @param string $valueKey $valueList中的值字段，例如"user_id"
     * @return array | collection
     */
    function put_collection_values($collection, $valueList, string $collectionKey, string $collectionNewKey, $valueKey)
    {
        if (!empty($collection) && (is_object($collection) || is_array($collection))) {
            $itemKeys = explode(".", $collectionKey);
            if (!empty($valueList)) {
                if (!is_object($valueList)) {
                    $valueList = collect($valueList);
                }
                $valueList = $valueList->keyBy($valueKey);
            }
            if (isset($collection[0])) {
                foreach ($collection as $index => $value) {
                    $collection[$index] = private_put_collection_values($value, $itemKeys, $valueList, $collectionNewKey);
                }
            } else {
                private_put_collection_values($collection, $itemKeys, $valueList, $collectionNewKey);
            }
        }
        return $collection;
    }
}

if (!function_exists('private_put_collection_values')) {
    /**
     * 对集合设置值，不对外公开
     * @param $collection
     * @param $itemKeys
     * @param $valueList
     * @param $collectionNewKey
     * @return mixed
     */
    function private_put_collection_values(&$collection, $itemKeys, $valueList, $collectionNewKey)
    {
        if (isset($collection[$itemKeys[0]])) {
            if (count($itemKeys) != 1) {
                $t = $itemKeys[0];
                unset($itemKeys[0]);
                $itemKeys = array_values($itemKeys);
                if (is_array($collection[$t]) || is_countable($collection[$t])) {
                    foreach ($collection[$t] as $k => $v) {
                        $collection[$t][$k] = private_put_collection_values($v, $itemKeys, $valueList, $collectionNewKey);
                    }
                } else {
                    $collection[$t] = private_put_collection_values($collection[$t], $itemKeys, $valueList, $collectionNewKey);
                }
            } else {
                if (isset($valueList[$collection[$itemKeys[0]]])) {
                    $collection[$collectionNewKey] = $valueList[$collection[$itemKeys[0]]];
                } else {
                    $collection[$collectionNewKey] = null;
                }
            }
        }
        return $collection;
    }
}
if (!function_exists('human_time')) {
    /**
     * 计算时间
     * @param $time 时间戳
     * @return string
     */
    function human_time($time)
    {
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }
        $time = abs($time);
        //计算天数
        $days = intval($time / 86400);
        if ($days != 0) {
            return $days . "天";
        }
        //计算小时数
        $remain = $time % 86400;
        $hours = intval($remain / 3600);
        if ($hours != 0) {
            return $hours . "小时";
        }
        //计算分钟数
        $remain = $time % 3600;
        $mins = intval($remain / 60);
        if ($mins != 0) {
            return $mins . "分钟";
        }
        //计算秒数
        $secs = $time % 60;
        return $secs . "秒";

    }
}
if (!function_exists('info')) {
    /**
     * 输出数据到控制台
     * @param mixed ...$arguments
     */
    function info(...$arguments)
    {
        var_dump(...$arguments);
    }
}

if (!function_exists('make_belong_relation_function')) {
    /**
     * 创建我属于关联关系的函数
     * @param string $relationName 关联关系的名字
     * @param array $mainModelColumns 使用关联关系的主表要筛选的列
     * @param array $relationColumns 关联关系的列 默认['id', 'name']
     * @param string $mainModelRelationKey 主表和关联关系对应的字段 空的话为$relationName+"_id"
     * @param callable|null $callback 内嵌级联调用
     * @return Closure 返回关联关系的匿名函数
     */
    function make_belong_relation_function($relationName, array &$mainModelColumns, $relationColumns = ['id', 'name'], $mainModelRelationKey = "", callable $callback = null)
    {
        $key = $mainModelRelationKey ? $mainModelRelationKey : $relationName . "_id";
        if (!in_array($key, $mainModelColumns)) array_push($mainModelColumns, $key);
        return make_has_relation_function($relationColumns, $callback);
    }
}
if (!function_exists('make_has_relation_function')) {
    /**
     * 创建我有关联关系的函数
     * @param array $relationColumns 关联关系的列 默认['id', 'name']
     * @param callable|null $callback 内嵌级联调用
     * @return Closure 返回关联关系的匿名函数
     * @return Closure
     */
    function make_has_relation_function($relationColumns = ['id', 'name'], callable $callback = null)
    {
        return function ($q) use ($relationColumns, $callback) {
            $q->select($relationColumns);
            if (is_callable($callback)) {
                $callback($q);
            }
        };
    }
}

if (!function_exists('int_to_chr')) {
    /**
     * 数字转字母 （类似于Excel列标）
     * @param mixed $index 索引值
     * @param int $start 字母起始值
     * @return string 返回字母
     */
    function int_to_chr($index, $start = 65)
    {
        $str = '';
        if (floor($index / 26) > 0) {
            $str .= int_to_chr(floor($index / 26) - 1);
        }
        return $str . chr($index % 26 + $start);
    }
}

if (!function_exists('check_diff_val')) {
    /**
     * 判断两个对象数组是否有不同的值
     * 后者里有前者的key时,但value不一样,返回true
     * 后者里没有前者的key,或有key,但value一样时,返回false
     * @param array $list
     * @param array $data
     * @return bool
     */
    function check_diff_val(array $list, array $data)
    {
        foreach ($list as $key => $val) {
            if (isset($data[$key]) && $data[$key]) {
                if (is_array($val)) {
                    if (check_diff_val($val, $data[$key])) {
                        return true;
                    }
                } else {
                    if ($list[$key] != $data[$key]) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}

if (!function_exists('get_diff_val')) {
    /**
     * 获取两个数组中key相同,value不同的数据
     * 返回后者的数据
     * @param array $list1
     * @param array $list2
     * @param array $excludeKey 排除的key数组
     * @return array
     * @author Zero
     */
    function get_diff_val(array $list1, array $list2, array $excludeKey = [])
    {
        $diff = [];
        foreach ($list1 as $key => $val) {
            if (!in_array($key, $excludeKey)) {
                if (isset($list2[$key]) && $list2[$key] != '') {
                    if (is_array($val)) {
                        $temp = get_diff_val($val, $list2[$key], $excludeKey);
                        !empty($temp) && $diff[$key] = $temp;
                    } else {
                        if ($list1[$key] != $list2[$key]) {
                            $diff[$key] = $list2[$key];
                        }
                    }
                }
            }
        }
        return $diff;
    }
}

if (!function_exists('to_camel_case')) {
    /**
     * 下划线命名转驼峰命名
     * 例：demo_function : demoFunction
     * @param $dirSep 分隔符
     * @param $str
     * @return mixed|string
     */
    function to_camel_case($dirSep, $str)
    {
        $array = explode($dirSep, $str);
        $result = $array[0];
        $len = count($array);
        if ($len > 1) {
            for ($i = 1; $i < $len; $i++) {
                $result .= ucfirst($array[$i]);
            }
        }
        return $result;
    }
}

if (!function_exists('mb_trim')) {
    /**
     * 去除两边带全角空格的字符串
     * @param $str
     * @return string
     * @author zero
     */
    function mb_trim($str)
    {
        mb_regex_encoding('utf-8');
        $str = mb_ereg_replace(' ', '', $str);
        return trim($str);
    }
}

if (!function_exists('str_replace_first')) {
    /**
     * 用替换字符串替换第一个出现的搜索字符串
     * @param mixed $search 搜索字符串
     * @param mixed $replace 替换字符串
     * @param mixed $subject 被替换字符串
     * @return string
     * @author zero
     */
    function str_replace_first($search, $replace, $subject)
    {
        if (($position = strpos($subject, $search)) !== false) {
            $replaceLen = strlen($search);
            $subject = substr_replace($subject, $replace, $position, $replaceLen);
        }
        return $subject;
    }
}

if (!function_exists('create_file_dir')) {
    /**
     * 检查当前目录是否存在 不存在就创建一个目录
     * @param mixed $dir 文件目录
     * @return bool
     * @author zero
     */
    function create_file_dir($dir)
    {
        return !is_dir($dir) && mkdir($dir, 0777, true);
    }
}

if (!function_exists('get_images_url')) {

    /**
     * 富文本获取图片信息
     * @param $str
     * @return mixed
     */
    function get_images_url($str)
    {
        preg_match('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i', $str, $match);
        return $match;
    }
}


if (!function_exists('download_file')) {
    /**
     * 下载远程文件
     * @param $url 远程文件路径
     * @param null $path 系统保存路径
     * @return string
     */
    function download_file($url, $path = null)
    {
        $filename = trim(pathinfo($url, PATHINFO_FILENAME));
        $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        $filename = "$filename.$ext";
        $root = config('server.settings.document_root', BASE_PATH . '/public');
        $path = $path ? $path : '/download/files';
        $savePath = $root . $path;
        if (!is_dir($savePath)) {
            // 判断路径是否存在,不存在,则创建
            mkdir($savePath, 0777, true);
        }
        $filePath = $savePath . '/' . $filename;
        if (!file_exists($filePath)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            $file = curl_exec($ch);
            curl_close($ch);
            $resource = fopen($filePath, 'a');
            fwrite($resource, $file);
            fclose($resource);
        }
        return $path . '/' . $filename;;
    }
}

if (!function_exists('download_file_stream')) {

    /**文件流保存到本地
     * @param $fileStream 文件流
     * @param null $path 保存路径
     * @return string
     */
    function download_file_stream($fileStream, $path = null)
    {
        $root = config('server.settings.document_root', BASE_PATH . '/public');
        $path = $path ? $path : '/download/files';
        $savePath = $root . $path;
        if (!is_dir($savePath)) {
            // 判断路径是否存在,不存在,则创建
            mkdir($savePath, 0777, true);
        }
        $fileName = '/' . date('YmdHis') . 'track' . '.pdf';
        file_put_contents($savePath . $fileName, base64_decode($fileStream), 1);
        return $path . $fileName;
    }
}
