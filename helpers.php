<?php

use \Demon\Library\Bomber;

//  修复神奇的精度问题
ini_set('serialize_precision', -1);

/**
 * 本次请求的秒时间戳
 */
define('DEMON_TIME', (int)$_SERVER['REQUEST_TIME']);

/**
 * 本次请求的毫秒时间戳
 */
define('DEMON_MSTIME', (int)($_SERVER['REQUEST_TIME_FLOAT'] * 1000));

/**
 * 本次请求的日期数值
 */
define('DEMON_DATE', (int)date('Ymd', DEMON_TIME));

/**
 * 本次请求的方法类型
 */
define('DEMON_METHOD', bomber()->requestMethod());

/**
 * 本次请求是否为AJAX请求
 */
define('DEMON_INAJAX', !strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'xmlhttprequest'));

/**
 * 本次请求是否为SUBMIT提交
 */
define('DEMON_SUBMIT', DEMON_METHOD == 'POST' && strtoupper(arguer('method') ?? '') != 'GET');

/**
 * 本次请求的唯一标识码
 */
define('DEMON_SOLECODE', (rand(1111, 9999) . (str_pad(DEMON_TIME, 12, 0, STR_PAD_LEFT)) . rand(111, 999)));

/**
 * 定义一些常用的状态码
 */
//  参数错误
define('DEMON_CODE_PARAM', 400);
//  会话失效
define('DEMON_CODE_AUTH', 401);
//  禁止使用
define('DEMON_CODE_FORBID', 403);
//  非法请求
define('DEMON_CODE_NONE', 404);
//  没有权限
define('DEMON_CODE_ACCESS', 405);
//  无法完成
define('DEMON_CODE_FAIL', 406);
//  等待超时
define('DEMON_CODE_TIME', 408);
//  条件错误
define('DEMON_CODE_COND', 412);
//  内容过大
define('DEMON_CODE_LARGE', 413);
//  媒体错误
define('DEMON_CODE_MEDIA', 415);
//  请求失效
define('DEMON_CODE_EXPIRED', 419);
//  请求频繁
define('DEMON_CODE_MANY', 429);
//  未知错误
define('DEMON_CODE_SERVER', 500);
//  数据错误
define('DEMON_CODE_DATA', 501);
//  无效服务
define('DEMON_CODE_SERVICE', 503);

/**
 * 通用方法
 * @return Bomber
 * @copyright 魔网天创信息科技
 * @author    ComingDemon
 */
function bomber()
{
    return (Bomber::instance());
}

if (!function_exists('dumper')) {
    /**
     * 调试输出(不中断)
     *
     * @param array ...$val
     *
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    function dumper(...$val)
    {
        if (!function_exists('dump')) {
            $dump = function($vars) {
                if (ini_get('html_errors')) {
                    $style = 'color:';
                    switch (gettype($vars)) {
                        case 'boolean':
                            $style .= '#d35900';
                            break;
                        case 'integer':
                            $style .= '#1fd300';
                            break;
                        case 'double':
                            $style .= '#00d3c1';
                            break;
                        case 'string':
                            $style .= '#ced300';
                            break;
                        case 'array':
                            $style .= '#afc1f7';
                            break;
                        case 'object':
                            $style .= '#c8aff7';
                            break;
                        case 'resource':
                            $style .= '#f76c6c';
                            break;
                        case 'NULL':
                            $style .= '#ff0000';
                            break;
                        default:
                            $style .= '#eaffd0';
                            break;
                    }
                    echo "<pre style='{$style}'>\n";
                    var_dump($vars);
                    echo "</pre>";
                }
                else {
                    var_dump($vars);
                }
            };
            echo '<style>html,body{background:#303030;color:#0ac571}</style>';
        }
        else
            $dump = function($vars) { dump($vars); };

        array_map(function($x) use ($dump) {
            $dump($x);
        }, func_get_args());
    }
}

if (!function_exists('debuger')) {
    /**
     * 调试输出(中断)
     *
     * @param array ...$val
     *
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    function debuger(...$val)
    {
        array_map(function($x) {
            dumper($x);
        }, func_get_args());

        die(1);
    }
}

if (!function_exists('mstime')) {
    /**
     * 获取毫秒运行时间
     *
     * @return int
     *
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    function mstime():int
    {
        return bomber()->mstime();
    }
}

if (!function_exists('msdate')) {
    /**
     * 获取毫秒运行时间
     *
     * @param        $format
     * @param string $timestamp
     *
     * @return false|mixed|string
     * @copyright 魔网天创信息科技
     * @author    ComingDemon
     */
    function msdate($format = 'Ymd', $timestamp = 0):string
    {
        return bomber()->msdate($format, $timestamp ? : mstime());
    }
}

/**
 * 错误生成器
 *
 * @param int    $code
 * @param string $message
 *
 * @return string
 * @copyright 魔网天创信息科技
 * @author    ComingDemon
 */
function error_build($code = DEMON_CODE_FAIL, $message = ''):string
{
    //  兼容两个参数对调位置仍然成立
    if (is_numeric($message) && $message > 0) {
        $cache = $code;
        $code = $message;
        $message = $cache;
    }

    return bomber()->errorBuild($code, $message);
}

/**
 * 错误检查器
 *
 * @param      $info
 * @param null $func
 *
 * @return bool|int|mixed|string
 * @copyright 魔网天创信息科技
 * @author    ComingDemon
 */
function error_check($info, $func = null)
{
    return bomber()->errorCheck($info, $func);
}

if (!function_exists('object_to_array')) {
    /**
     * 对象转换为数组
     *
     * @param $object
     *
     * @return array
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    function object_to_array($object):array
    {
        return bomber()->objectToArray($object);
    }
}

if (!function_exists('array_to_object')) {
    /**
     * 数组转换为对象
     *
     * @param array $array
     *
     * @return object
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    function array_to_object(array $array)
    {
        return bomber()->arrayToObject($array);
    }
}

/**
 * 快速获取请求数据（可以携带自定义参数）
 *
 * @param        $name
 * @param        $default
 * @param string $filter
 * @param        $data
 *
 * @return array|mixed|string|object|int|float
 * @copyright 魔网天创信息科技
 *
 * @author    ComingDemon
 */
function arguer($name = null, $default = null, $filter = '', $data = null)
{
    return $data !== null ? bomber()->arguer($data, $name, $default, $filter) : bomber()->input($name, $default, $filter);
}
