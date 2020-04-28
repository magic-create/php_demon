<?php

use \Demon\Library\Bomber;

//	修复神奇的精度问题
ini_set('serialize_precision', -1);
//  本次请求的秒时间戳
define('DEMON_TIME', (int)$_SERVER['REQUEST_TIME']);
//  本次请求的毫秒时间戳
define('DEMON_MSTIME', (int)($_SERVER['REQUEST_TIME_FLOAT'] * 1000));
//  本次请求的日期数值
define('DEMON_DATE', (int)date('Ymd', DEMON_TIME));
//  本次请求的方法类型
define('DEMON_METHOD', bomber()->request_method());
//  本次请求是否为AJAX请求
define('DEMON_INAJAX', !strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'xmlhttprequest'));
//  本次请求是否为SUBMIT提交
define('DEMON_SUBMIT', DEMON_METHOD == 'POST' && ($_REQUEST['method'] ?? '') != 'get');
//  本次请求的唯一标识码
define('DEMON_SOLECODE', (rand(1111, 9999) . (str_pad(DEMON_TIME, 12, 0, STR_PAD_LEFT)) . rand(111, 999)));

if (!function_exists('dumper')) {

    /**
     * 调试输出(不中断)
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     * @param array ...$val
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
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     * @param array ...$val
     */
    function debuger(...$val)
    {
        array_map(function($x) {
            dumper($x);
        }, func_get_args());

        die(1);
    }
}

/**
 * 通用方法
 * @author    ComingDemon
 * @copyright 魔网天创信息科技
 * @return Bomber|mixed
 */
function bomber()
{
    $bomber = Bomber::instance();
    //  获取参数
    $parm = func_get_args();
    //  如果有参数
    if (isset($parm[0])) {
        switch ($parm[0]) {
            //  对象转数组
            case 'array':
                return $bomber->object_to_array($parm[1]??new stdClass());
                break;
            //  数组转对象
            case 'object':
                return $bomber->array_to_object($parm[1]??[]);
                break;
            //  格式化毫秒时间戳
            case 'msdate':
                return $bomber->msdate($parm[1]??'Y-m-d H:i:s.f', $parm[2]??DEMON_MSTIME);
                break;
            //  检查数据格式
            case 'check':
                return $bomber->data_check($parm[1]??null, $parm[2]??null);
                break;
            default:
                return $bomber;
        }
    }

    return $bomber;
}

/**
 * 快速获取请求数据（可以携带自定义参数）
 * @author    ComingDemon
 * @copyright 魔网天创信息科技
 *
 * @param        $name
 * @param        $default
 * @param string $filter
 * @param        $data
 *
 * @return array|mixed|string|object|int|float
 */
function arguer($name = null, $default = null, $filter = '', $data = null)
{
    return $data !== null ? bomber()->arguer($data, $name, $default, $filter) : bomber()->input($name, $default, $filter);
}