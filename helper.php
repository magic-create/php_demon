<?php

if (! function_exists('dumper')) {

/**
 * 调试输出(不中断)
 * @author    ComingDemon
 * @copyright 魔网天创信息科技
 *
 * @param array ...$val
 */
function dumper(...$val)
{
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
    array_map(function($x) use ($dump) {
        $dump($x);
    }, func_get_args());
}
}

if (! function_exists('debuger')) {
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

if (! function_exists('conver')) {
/**
 * 转换方法
 * @author    ComingDemon
 * @copyright 魔网天创信息科技
 * @return Convert|mixed
 */
function conver()
{
    $convert = Convert::instance();
    //  获取参数
    $parm = func_get_args();
    //  如果有参数
    if (isset($parm[0])) {
        switch ($parm[0]) {
            //  对象转数组
            case 'array':
                return $convert->object_to_array($parm[1]??new stdClass());
                break;
            //  数组转对象
            case 'object':
                return $convert->array_to_object($parm[1]??[]);
                break;
            //  格式化毫秒时间戳
            case 'msdate':
                return $convert->msdate($parm[1]??'Y-m-d H:i:s.f', $parm[2]??DEMON_MSTIME);
                break;
            default:
                return $convert;
        }
    }

    return $convert;
}
}