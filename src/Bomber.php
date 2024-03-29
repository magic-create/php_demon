<?php
/**
 * 本文件用于定义一些通用相关的内容
 * Created by M-Create.Team,
 * Copyright 魔网天创信息科技
 * User: ComingDemon
 * Date: 2019/3/14
 * Time: 10:21
 */

namespace Demon\Library;

use Closure;
use stdClass;
use voku\helper\AntiXSS;

class Bomber
{
    /**
     * @var object 对象实例
     */
    protected static $instance;

    /**
     * 初始化
     * @return Bomber
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    static public function instance()
    {
        if (is_null(self::$instance))
            self::$instance = new static();

        return self::$instance;
    }

    /**
     * 获取项目根目录
     *
     * @param string $path
     *
     * @return string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function root($path = '')
    {
        //  如果是命令行模式
        if (PHP_SAPI == 'cli') {
            $dir = explode(str_replace('/', DIRECTORY_SEPARATOR, '/comingdemon/demon/src'), __DIR__);
            $_SERVER['DOCUMENT_ROOT'] = $dir[0];
        }

        return dirname($_SERVER['DOCUMENT_ROOT']) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * 对象合并
     * @return mixed
     * @copyright 魔网天创信息科技
     *
     *
     * @author    ComingDemon
     */
    public function objectMerge()
    {
        //  获取参数
        $parm = func_get_args();
        $tempArray = [];
        foreach ($parm as $key => $val)
            $tempArray = array_merge($tempArray, (array)$val);

        return (object)$tempArray;
    }

    /**
     * 获取对象全部键
     *
     * @param $object
     *
     * @return array
     *
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function objectKeys($object)
    {
        $keys = [];
        foreach ($object as $key => $val)
            $keys[] = $key;

        return $keys;
    }

    /**
     * 对象克隆
     *
     * @param $object
     *
     * @return mixed
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function objectClone($object)
    {
        return self::arrayToObject(self::objectToArray($object));
    }

    /**
     * 对象排序
     *
     * @param $object
     * @param $type
     *
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function objectSort(&$object, $type)
    {
        if ($object) {
            $object = self::objectToArray($object);
            $type($object);
            $object = (object)self::arrayToObject($object);
            //  新对象
            $newObject = new stdClass();
            //  保证对象是文本键名
            foreach ($object as $key => $val)
                $newObject->{(string)$key} = $val;
            $object = $newObject;
        }
    }

    /**
     * 对象过滤器
     *
     * @param array $object //  对象或数组
     * @param array $filter //  过滤器数组
     * @param int   $mod    //  小于0表示黑名单模式，大于0表示白名单模式
     *
     * @return array
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function objectFilter(&$object = [], $filter = [], $mod = -1)
    {
        //  是数组还是对象呢？
        $dataType = '';
        //  是数组
        if (is_array($object))
            $dataType = 'array';
        //  是对象
        if (is_object($object))
            $dataType = 'object';
        //  如果什么都不是
        if (!$dataType)
            $object = null;
        //  如果符合条件，就准备遍历
        if (in_array($dataType, ['array', 'object'])) {
            //  遍历一下
            foreach ($object as $key => $val) {
                //  如果是黑名单模式并且在黑名单则销毁
                if ($mod < 0 && in_array($key, $filter)) {
                    if ($dataType == 'array')
                        unset($object[$key]);
                    else if ($dataType == 'object')
                        unset($object->{$key});
                    continue;
                }
                //  如果是白名单模式并且不在白名单则销毁
                else if ($mod > 0 && !in_array($key, $filter)) {
                    if ($dataType == 'array')
                        unset($object[$key]);
                    else if ($dataType == 'object')
                        unset($object->{$key});
                    continue;
                }
            }
        }

        //  返回最终结果
        return $object;
    }

    /**
     * 统计对象成员数量
     *
     * @param $object
     *
     * @return int
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function objectCount($object)
    {
        return count(self::objectToArray($object));
    }

    /**
     * 将对象转换为数组
     *
     * @param $object //对象
     *
     * @return mixed
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function objectToArray($object)
    {
        return json_decode(json_encode($object), true);
    }

    /**
     * 将数组转换为对象
     *
     * @param $array //数组
     *
     * @return mixed
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function arrayToObject($array)
    {
        return json_decode(json_encode($array));
    }

    /**
     * XML转换为对象
     *
     * @param $xml
     *
     * @return mixed
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function xmlToObject($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        //  返回结果
        $object = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)));
        //  进入递归（将子节点的item标签设置为主节点内容）
        self::_xmlToObject($object);

        //  返回结果
        return $object;
    }

    private function _xmlToObject(&$data)
    {
        //  如果是对象或者数组，则递归进去
        if (is_object($data) || is_array($data)) {
            //  开始循环
            foreach ($data as $key => $val) {
                //  如果是对象或者数组，则递归进去
                if (is_object($val) || is_array($val)) {
                    //  开始循环查找子节点
                    foreach ($val as $key2 => $val2) {
                        //  子节点为数组，则将内容直接赋值到父节点
                        if (is_array($val2))
                            $data->{$key} = $val2;
                    }
                }
                //  继续递归
                self::_xmlToObject($val);
            }
        }
    }

    /**
     * 对象转换为XML
     *
     * @param        $object
     * @param null   $doccment
     * @param null   $item
     * @param string $root
     * @param bool   $isFormat
     *
     * @return string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function objectToXml($object, $doccment = null, $item = null, $root = 'xml', $isFormat = true)
    {
        //  首次申明对象
        if (!$doccment) {
            $doccment = new \DOMDocument('1.0');
            $doccment->encoding = 'UTF-8';
            $object = self::objectToArray($object);
        }
        //  首次申明结构体
        if (!$item) {
            $item = $doccment->createElement($root);
            $doccment->appendChild($item);
        }
        //  循环插入节点
        foreach ($object as $key => $val) {
            $itemx = $doccment->createElement(is_string($key) ? $key : 'item');
            $item->appendChild($itemx);
            if (!is_array($val)) {
                $text = $doccment->createTextNode($val);
                $itemx->appendChild($text);
            }
            else self::objectToXml($val, $doccment, $itemx);
        }
        //  生成结果
        $data = $doccment->saveXML();

        //  如果非格式化，则去掉标签头再返回
        return !$isFormat ? str_replace(['<?xml version="1.0" encoding="UTF-8"?>', "\n"], '', $data) : $data;
    }

    /**
     * 计算数字长度
     *
     * @param $num
     *
     * @return int
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function numCount($num)
    {
        return strlen((int)($num));
    }

    /**
     * 检查字符串是否包含数组中某个值
     *
     * @param array|string $string
     * @param array|string $arrry
     * @param bool|false   $returnValue
     *
     * @return bool
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function arrayStrpos($string, $arrry, $returnValue = false)
    {
        if (empty($string) || empty($arrry))
            return false;

        //  第一个参数传入数组则表示从第一个参数中循环查找第二个参数字符串是否存在任意第一参数成员中，如 arrayStrpos(['apples','bananas'], 'apple') = true
        if (is_array($string))
            foreach ($string as $val) {
                if (mb_stripos($val, $arrry) !== false) {
                    $return = $returnValue ? $val : true;

                    return $return;
                }
            }
        //  第一个参数传入字符串则表示从第二个参数数组中循环查找任意成员是否被包含在第一个字符串参数中，如 arrayStrpos('apples', ['apple','banana']) = true
        else foreach ((array)$arrry as $val) {
            if (mb_stripos($string, $val) !== false) {
                $return = $returnValue ? $val : true;

                return $return;
            }
        }

        return false;
    }

    /**
     *   修改数字下标为内容中的键值
     *
     * @param        $array
     * @param string $index
     *
     * @return array
     * @copyright   魔网天创信息科技
     *
     * @author      ComingDemon
     */
    public function arrayIndex($array, $index = '')
    {
        $isObj = false;
        if ($index) {
            if (is_object($array)) {
                $isObj = true;
                $array = self::objectToArray($array);
            }
            $array = array_values($array);
            $newArray = [];
            foreach ($array as $val) {
                if (is_object($array[0]))
                    $newArray[$val->{$index}] = self::arrayToObject($val);
                else
                    $newArray[$val[$index]] = $val;
            }

            return $isObj ? self::arrayToObject($newArray) : $newArray;
        }

        return $array;
    }

    /**
     * 获取数组维度
     *
     * @param $array
     *
     * @return mixed
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function arrayLevel($array)
    {
        $arraylevel = [0];
        self::_arrayLevel($array, $arraylevel, 0);

        return max($arraylevel);
    }

    private function _arrayLevel($array, &$arraylevel, $level = 0)
    {
        if (is_array($array)) {
            $level++;
            $arraylevel[] = $level;
            foreach ($array as $val)
                self::_arrayLevel($val, $arraylevel, $level);
        }
    }

    /**
     * 判断数组是否顺序
     *
     * @param      $array
     * @param bool $strict
     *
     * @return bool
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function arrayOrderly($array, $strict = false)
    {
        $index = $max = $strict ? 0 : min(array_keys($array));
        foreach ($array as $key => $val) {
            if ($strict ? $key != $index : $key < $max || !is_numeric($key))
                return false;
            $index += 1;
            $max = max($index, $key);
        }

        return true;
    }

    /**
     * 数组随机获取内容
     *
     * @param array  $array
     * @param int    $count
     * @param string $type value(返回值)|full(返回全部)|key(返回键)
     *
     * @return mixed|null
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function arrayRand($array = [], $count = 1, $type = 'value')
    {
        if (!$array)
            return null;
        if (is_object($array))
            $array = self::objectToArray($array);
        $count = min(count($array), $count);
        $rand = array_rand($array, $count);
        if ($count == 1)
            $rand = [$rand];
        //  返回键
        if ($type == 'key')
            return $count == 1 ? $rand[0] : $rand;
        $out = [];
        foreach ($rand as $k)
            $out[$k] = $array[$k];
        //  返回全部
        if ($type == 'full')
            return $out;
        $out = array_values($out);

        //  返回值
        return $count == 1 ? $out[0] : $out;
    }

    /**
     * 数组排序
     * @return bool|mixed|null
     * @copyright 魔网天创信息科技
     * @author    ComingDemon
     */
    public function arrayReorder()
    {
        //  array_reorder($array,$field1,$order1,$field2,$order2,...)
        $args = func_get_args();
        //  参数无效直接返回空
        if (!$args)
            return false;
        //  出栈
        $arr = array_shift($args);
        //  如果不是数组则返回错误
        if (!is_array($arr))
            return false;
        //  循环处理
        foreach ($args as $key => $field) {
            //  字段名必须是字符串
            if (is_string($field)) {
                $foo = [];
                foreach ($arr as $index => $val)
                    $foo[$index] = $val[$field];
                $args[$key] = $foo;
            }
        }
        $args[] = &$arr;
        //  引用值
        call_user_func_array('array_multisort', $args);

        //  返回入栈内容
        return array_pop($args);
    }

    /**
     * 删除数组指定元素
     *
     * @param array $array  //原数组
     * @param array $array2 //需要去掉的值
     * @param bool  $reset  //是否重排
     *
     * @return array
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function arrayUnset(&$array = [], $array2 = [], $reset = true)
    {
        //  强转第二个参数为数组
        if (!is_array($array2))
            $array2 = [$array2];
        //  遍历匹配删除
        foreach ($array2 as $key => $val) {
            foreach ($array as $key2 => $val2) {
                if ($val2 == $val) {
                    unset($array[$key2]);
                    continue;
                }
            }
        }

        //  返回处理后数组（是否重新排列数组键）
        return $reset ? array_merge($array) : $array;
    }

    /**
     * 对比先后差异
     *
     * @param          $data
     * @param          $info
     * @param string[] $alias
     *
     * @return array|mixed
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function arrayDiffer($data, $info, $alias = ['before' => 'before', 'after' => 'after'])
    {
        //  容器别名
        $before = ($alias['before'] ?? '') ? : 'before';
        $after = ($alias['after'] ?? '') ? : 'after';
        //  数据类型
        $type = 'array';
        if (!is_array($data)) {
            $type = 'object';
            $data = self::objectToArray($data);
        }
        $info = $info ? (is_array($info) ? $info : self::objectToArray($info)) : [];
        $list = [];
        //  匹配先后差异
        foreach ($data as $key => $val) {
            try {
                if (!isset($info[$key]) || $info[$key] != $val) {
                    $foo = $info[$key] ?? null;
                    if ($foo != $val)
                        $list[$key] = [$before => $foo, $after => $val];
                }
            } catch (\Exception $exception) {
                //  不处理异常
            }
        }

        //  返回差异结果
        return $type == 'array' ? $list : self::arrayToObject($list);
    }

    /**
     * 查找元素在数组中的位置
     *
     * @param      $needle
     * @param      $haystack
     * @param bool $alias
     *
     * @return bool|false|int|string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function arraySearch($needle, $haystack, $alias = false)
    {
        if ($alias) {
            foreach (self::objectToArray($haystack) as $index => $data)
                if ($data[$alias] == $needle)
                    return $index;

            return false;
        }
        else return array_search($needle, $haystack);
    }

    /**
     * 创建一个随机字符串
     *
     * @param int    $length //随机长度
     * @param string $type   //随机类型
     *
     * @return string
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function rand($length = 6, $type = 'all')
    {
        $key = '';
        if ($length < 1)
            return '';
        // 中文
        if ($type == 'chinese') {
            for ($i = 0; $i < $length; $i++)
                $key .= iconv('GB2312', 'UTF-8', chr(mt_rand(0xB0, 0xD0)) . chr(mt_rand(0xA1, 0xF0)));
        }
        else {
            switch ($type) {
                //  全部
                case 'all':
                    $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    break;
                //  数字
                case 'num':
                    $pattern = '1234567890';
                    break;
                //  字母
                case 'letter':
                    $pattern = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    break;
                //  自定义
                default:
                    $pattern = $type;
            }
            $pattern = self::strSplit($pattern);
            for ($i = 0; $i < $length; $i++)
                $key .= $pattern[mt_rand(0, count($pattern) - 1)];
        }

        //  返回字符串
        return (string)$key;
    }

    /**
     * 通过密码生成一个加密密码
     *
     * @param $parm //  参数
     *
     * @return mixed
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function password($parm = [])
    {
        //  传入内容
        $content = $parm['content'] ?? '';
        //  是否预先MD5处理
        $md5 = $parm['md5'] ?? false;
        //  如果已经是32位的MD5则截取
        if ($md5 === 32 || self::regexp($content, 'md5'))
            $content = strtolower(substr($content, 8, 16));
        //  如果不是MD5则加密为16位小写MD5
        else if (!$md5)
            $content = strtolower(self::md5($content));

        //  操作动作
        $action = $parm['action'] ?? 'hash';
        switch ($action) {
            //  创建加密
            case 'hash':
                return password_hash($content, PASSWORD_BCRYPT);
                break;
            //  校验加密
            case 'verify':
                return password_verify($content, $parm['hash'] ?? '');
                break;
        }

        //  什么也没发生
        return null;
    }

    /**
     * 转换为指定精度
     *
     * @param        $value //数值
     * @param int    $long  //精度位数
     * @param string $type  //截取类型
     *
     * @return string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function doublePrecision($value, $long = 2, $type = 'floor')
    {
        //  精度
        $pow = pow(10, $long);
        //  转换为字符串（解决科学计数法展示）
        $value = $type != 'science' ? self::doublePrecision($value, $long, 'science') : $value;
        switch ($type) {
            case 'science':
                if (stripos($value, 'e') !== false) {
                    $a = explode('e', strtolower($value));
                    $value = bcmul($a[0], bcpow(10, $a[1], $long + 1), $long + 1);
                }
                break;
            case 'floor':
                return number_format(floor(bcmul($pow, $value)) / $pow, $long, '.', '');
            case 'round':
                return self::doublePrecision(round($value, $long), $long);
            case 'ceil':
                return number_format(ceil(bcmul($pow, $value, $long)) / $pow, $long, '.', '');
            case 'integer':
                return self::doublePrecision(floor($value), $long);
            case 'decimals':
                return $long < 0 ? (string)($value - floor($value)) : self::doublePrecision($value - floor($value), $long);
            case 'repair':
                return round($value * $pow) / $pow;
        }

        //  返回处理结果
        return $value;
    }

    /**
     * 修复精度问题
     *
     * @param     $value
     * @param int $long
     *
     * @return float|int
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function doubleRepair($value, $long = 2)
    {
        //  计算比例
        $ratio = pow(10, $long);

        //  返回结果
        return round($value * $ratio) / $ratio;
    }

    /**
     * 获取随机小数
     *
     * @param     $min
     * @param     $max
     * @param int $scale
     *
     * @return float|int|string
     *
     * @copyright 魔网天创信息科技
     * @author    ComingDemon
     */
    public function doubleRand($min, $max, $scale = 0)
    {
        $isString = is_string($max);
        $sMin = $isString ? $min : strval($min);
        $sMax = $isString ? $max : strval($max);
        //  对比大小
        switch (bccomp($sMin, $sMax, $scale * 2)) {
            //  相等
            case 0:
                return $min;
                break;
            //  相反
            case 1:
                [$sMin, $sMax] = [$sMax, $sMin];
                break;
        }
        //  随机数
        $rand = self::doublePrecision(bcadd($sMin, bcmul(mt_rand() / mt_getrandmax(), bcsub($sMax, $sMin, $scale), $scale + 1), $scale + 1), $scale, 'round');

        //  返回结果
        return $isString ? $rand : (double)$rand;
    }

    /**
     * 数字节点转字符串
     *
     * @param            $number //原数字
     * @param int        $digit  //节点位数
     * @param string     $unit   //节点字符
     * @param bool|false $force  //是否强制(不判断符合位数)
     *
     * @return string
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function numToChar($number, $digit = 4, $unit = 'k', $force = false)
    {
        //  判断数字是否达到转换标准
        $minNumber = pow(10, $digit);
        if ($number > $minNumber * 10 || $force)
            return (floor($number / $minNumber * 10) / 10) . $unit;
        else return $number;
    }

    /**
     * 数字最大值返回
     *
     * @param        $number
     * @param        $max
     * @param string $string
     *
     * @return string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function numToMax($number, $max, $string = '')
    {
        return min($number, $max) == $max ? ($string ? : $max . '+') : $number;
    }

    /**
     * 取摘要信息
     *
     * @param $string
     * @param $length
     * @param $charset
     *
     * @return mixed|string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function digest($string, $length = PHP_INT_MAX, $charset = 'utf-8')
    {
        return mb_substr(str_replace(["\r", "\n", "\r\n", PHP_EOL, '&nbsp;'], '', trim(strip_tags((string)nl2br($string)))), 0, $length, $charset ? : 'utf-8');
    }

    /**
     * 生成一个订单码
     *
     * @param int    $length
     * @param string $pre
     *
     * @return string
     * @copyright 魔网天创信息科技
     * @author    ComingDemon
     */
    public function orderBuild($length = 24, $pre = '')
    {
        //  根据前缀来识别随机串长度，前缀长度建议4位就行了
        $remain = $length - mb_strlen($pre);
        if ($remain <= 0)
            return mb_substr($pre, 0, $length);
        //  一般时间长度为14位
        $string = $pre . date('YmdHis');
        $remain = $length - mb_strlen($string);
        if ($remain <= 0)
            return mb_substr($string, 0, $length);

        //  返回拼接随机内容
        return $string . self::rand($remain, 'num');
    }

    /**
     * 邀请码生成和解码
     *
     * @param        $content //用户UID或者邀请码
     * @param int    $type    //生成或解码
     * @param string $seed    //字符串编排串种子
     * @param int    $offset  //UID偏移量
     *
     * @return float|int|string
     *
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function inviteCode($content, $type = 1, $seed = '', $offset = 0)
    {
        //  字符编排内容
        $seed = $seed ? : 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        //  生成
        if ($type != 0) {
            $seed = self::strSplit($seed);
            $code = '';
            $content += $offset;
            while ($content > 0) {
                $mod = $content % count($seed);
                $content = ($content - $mod) / count($seed);
                $code = $seed[$mod] . $code;
            }

            return $code;

        }
        //  解码
        else {
            $content = self::strSplit(self::strRev($content));
            $uid = 0;
            for ($i = 0; $i < count($content); $i++)
                $uid += mb_strpos($seed, $content[$i]) * pow(mb_strlen($seed), $i);

            return $uid - $offset;
        }
    }

    /**
     * 字符串替换一次
     *
     * @param        $needle
     * @param        $replace
     * @param        $haystack
     * @param string $position
     *
     * @return string
     *
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function strReplaceOnce($needle, $replace, $haystack, $position = 'left')
    {
        //  根据位置来清理
        $position = strtolower($position);
        switch ($position) {
            //  前缀
            case 'pre':
                $start = mb_strpos($haystack, $needle) === 0 ? 0 : false;
                break;
            //  后缀
            case 'post':
                $start = mb_substr($haystack, -mb_strlen($needle)) === $needle ? -mb_strlen($needle) : false;
                break;
            //  从右侧
            case 'right':
                $start = mb_strrpos($haystack, $needle);
                break;
            //  从左侧
            case 'left':
            default:
                $start = mb_strpos($haystack, $needle);
                break;
        }
        //  如果没有匹配的内容
        if ($start === false)
            return $haystack;

        //  开始替换
        $regex = '`' . preg_quote($needle, '`') . '`';
        switch ($position) {
            //  从右开始搜索替换
            case 'post':
            case 'right':
                $total = mb_substr_count($haystack, $needle);
                $count = 0;

                return preg_replace_callback($regex, function($matches) use ($total, &$count, $needle, $replace) {
                    $count++;
                    if ($count == $total)
                        return $replace;

                    return $needle;
                }, $haystack);
                break;
            //  从左开始搜索替换
            case 'pre':
            case 'left':
            default:
                return preg_replace($regex, $replace, $haystack, 1);
                break;
        }
    }

    /**
     * 字符串占位符替换
     *
     * @param          $str
     * @param array    $replace
     * @param string[] $placeholder
     *
     * @return string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function strReplacement($str, $replace = [], $placeholder = [':', ''])
    {
        if (!$replace || !is_array($replace))
            return $str;
        if (!self::arrayOrderly($replace))
            array_multisort($replace, SORT_ASC, array_map(function($value, $key) { return mb_strlen($key) * -1; }, $replace, array_keys($replace)));
        $prefix = ($placeholder[0] ?? '');
        $postfix = ($placeholder[1] ?? '');
        foreach ($replace as $key => $value)
            $str = str_replace([$prefix . $key . $postfix, $prefix . mb_strtoupper($key) . $postfix, $prefix . mb_strtoupper(mb_substr($key, 0, 1)) . mb_substr($key, 1) . $postfix], [$value, mb_strtoupper($value), mb_strtoupper(mb_substr($value, 0, 1)) . mb_substr($value, 1)], $str);

        return $str;
    }

    /**
     * 字符串过长截取
     *
     * @param $str
     * @param $length
     * @param $suffix
     *
     * @return string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function strCut($str, $length, $suffix = '...')
    {
        return mb_strimwidth($str, 0, $length, $suffix, 'utf-8');
    }

    /**
     * 字符串加掩码
     *
     * @param       $name
     * @param array $parm
     *
     * @return string
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function strMask($name, $parm = [])
    {
        //  字符集
        $encoding = strtolower($parm['encoding'] ?? 'utf-8');
        //  定义掩码
        $mask = $parm['mask'] ?? '*';
        //  前缀长度
        $firstLength = $parm['first'] ?? 1;
        //  后缀长度
        $lastLength = $parm['last'] ?? 1;
        //  当不足时哪个方向设空
        $reserve = strtolower($parm['reserve'] ?? 'first');
        //  最小长度
        $minLength = $parm['min'] ?? null;
        //  最大长度
        $maxLength = $parm['max'] ?? null;
        //  固定长度
        $fixLength = $parm['length'] ?? null;
        //  获取当前字符长度
        $strlen = mb_strlen($name, $encoding);
        //  当长度恰好或小于设定的长度则将小方向设置为空
        $firstLength = $firstLength >= $strlen ? 0 : $firstLength;
        $lastLength = $lastLength >= $strlen ? 0 : $lastLength;
        if ($strlen <= $firstLength + $lastLength)
            $reserve == 'auto' ? ($firstLength >= $lastLength ? $lastLength = 0 : $firstLength = 0) : ($reserve == 'first' ? $lastLength = 0 : $firstLength = 0);
        //  明文长度
        $showLength = $firstLength + $lastLength;
        //  截取前面的字符
        $firstStr = mb_substr($name, 0, $firstLength, $encoding);
        $lastStr = mb_substr($name, -($lastLength), $lastLength, $encoding);
        //  掩码长度
        $maskLength = $fixLength ? $fixLength - $showLength : $strlen - $showLength;
        $maskLength = $maxLength ? min($maxLength - $showLength, $maskLength) : $maskLength;
        $maskLength = $minLength ? max($minLength - $showLength, $maskLength) : $maskLength;
        //  掩码拼接
        $string = $firstStr . self::strFill($maskLength, null, 'right', $mask) . $lastStr;

        //  返回结果
        return $string;
    }

    /**
     * 获取字符编码
     *
     * @param $string
     *
     * @return string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function strCode($string)
    {
        //  转换为GBK
        $gbk = iconv('UTF-8', 'GBK', $string);

        //  如果相同则表示原来就是UFT8否则就是GBK
        return iconv('GBK', 'UTF-8', $gbk) == $string ? 'UTF-8' : 'GBK';
    }

    /**
     * 获取字符长度（中文一律当做2个字符统计）
     *
     * @param $string
     *
     * @return int
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function strLen($string)
    {
        //  如果是UTF8就转换为GBK再计算
        if (self::strCode($string) == 'UTF-8')
            $string = iconv('UTF-8', 'GBK', $string);

        return strlen($string);
    }

    /**
     * 字符串填充（类似str_pad，但支持中文）
     *
     * @param $pre
     * @param $str
     * @param $type
     * @param $fill
     *
     * @return string
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function strFill($pre, $str, $type = 'left', $fill = '0')
    {
        //  获取当前字符串长度
        $strlen = mb_strlen($str);
        //  如果不足位数
        if ($strlen < $pre) {
            $differ = $pre - $strlen;
            switch (strtolower($type)) {
                //  右侧补齐
                case 'right':
                case STR_PAD_RIGHT:
                    return $str . str_repeat($fill, $differ);
                    break;
                //  左侧补齐
                case 'left':
                case STR_PAD_LEFT:
                    return str_repeat($fill, $differ) . $str;
                    break;
                //  居中补齐
                case 'center':
                case STR_PAD_BOTH:
                default:
                    return str_repeat($fill, $differ / 2) . $str . str_repeat($fill, $differ - $differ / 2);
                    break;
            }
        }

        //  直接返回
        return $str;
    }

    /**
     * 字符串转图片
     *
     * @param        $str
     * @param string $type
     * @param array  $parm
     *
     * @return string
     *
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function strImage($str, $type = 'svg', $parm = [])
    {
        //  动态背景色
        $calc = $parm['calc'] ?? false;
        //  背景颜色
        $background = $parm['background'] ?? '#ffffff';
        //  文字颜色
        $color = $parm['color'] ?? '#000000';
        if ($calc) {
            $total = unpack('L', hash('adler32', $str, true))[1];
            $conver = (new Color())->hsv($total % 360 / 360, 0.3, 0.9);
            $background = '#' . $conver->hsv2hex()->hex;
            $color = '#ffffff';
        }
        //  只取首字
        $str = ($parm['substr'] ?? false) ? mb_strtoupper(mb_substr($str, 0, $parm['substr'])) : $str;
        //  字体文件
        $font = $parm['font'] ?? '';
        //  高度
        $height = $parm['height'] ?? 128;
        //  字体大小
        $size = $parm['size'] ?? $height / 2;
        //  宽度
        $width = $parm['width'] ?? null;
        if ($width === null) {
            $i = 0;
            foreach (mb_str_split($str) as $val)
                $i += (self::regexp(strlen($val), 'chs') ? 1 : (is_numeric($val) ? 0.64 : 0.84));
            $width = max($height, ($i - 1) * $size + $height);
        }
        //  微调位置
        $offsetX = $parm['offsetX'] ?? 0;
        $offsetY = $parm['offsetX'] ?? 0;
        //  图片格式
        $type = strtolower($type);
        switch ($type) {
            //  SVG
            case 'svg':
            case 'xml':
            case 'svg+xml':
                $type = 'svg+xml';
                $content = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="' . $width . '" height="' . $height . '"><rect fill="' . $background . '" x="0" y="0" width="' . $width . '" height="' . $height . '"></rect><text x="' . ($width / 2) . '" y="' . ($height / 2) . '" font-size="' . $size . '" text-copy="comingdemon" fill="' . $color . '" text-right="demon" text-anchor="middle" alignment-baseline="central">' . $str . '</text></svg>';
                break;
            //  其他
            default:
                //  字体大小单位
                $size = $size / 96 * 72;
                //  创建图片
                $img = imagecreate($width, $height);
                //  设置背景色
                imagecolorallocate($img, hexdec(substr($background, 1, 2)), hexdec(substr($background, 3, 2)), hexdec(substr($background, 5, 2)));
                if ($font && !is_file($font)) {
                    //  设置文字颜色
                    $textColor = imagecolorallocate($img, hexdec(substr($color, 1, 2)), hexdec(substr($color, 3, 2)), hexdec(substr($color, 5, 2)));
                    //  设置文字样式
                    imagettftext($img, $size, 0, ($height - $size) / (self::regexp(mb_substr($str, 0, 1), 'chs') ? 2.5 : 2.3) + $offsetX, ($height + $size) / 2 + $offsetY, $textColor, $font, $str);
                }
                //  打开缓冲区
                ob_start();
                //  支持类型
                switch ($type) {
                    case 'png':
                        imagepng($img);
                        break;
                    case 'gif':
                        imagegif($img);
                        break;
                    case 'bmp':
                        self::imagebmp($img);
                        break;
                    case 'jpg':
                    case 'jpeg':
                    default :
                        $type = 'jpeg';
                        imagejpeg($img);
                }
                //  获取缓冲区内容
                $content = ob_get_contents();
                //  清理缓冲区
                ob_end_clean();
                break;
        }

        //  返回内容
        return "data:image/{$type};base64," . base64_encode($content);
    }

    /**
     * 字符串切割为数组
     *
     * @param string $str
     * @param int    $gap
     * @param string $encoding
     *
     * @return mixed
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function strSplit(string $str = '', int $gap = 1, $encoding = 'utf-8')
    {
        //  计算字符长度
        $strlen = mb_strlen($str, $encoding);
        //  循环截取
        while ($strlen) {
            $array[] = mb_substr($str, 0, $gap, $encoding);
            $str = mb_substr($str, $gap, $strlen, $encoding);
            $strlen = mb_strlen($str, $encoding);
        }

        //  返回数组
        return $array;
    }

    /**
     * 字符串翻转或打乱
     *
     * @param string $str
     * @param bool   $shuffle
     *
     * @return string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function strRev(string $str = '', $shuffle = false)
    {
        $split = self::strSplit($str);
        if ($shuffle)
            shuffle($split);
        else
            krsort($split);

        return join('', $split);
    }

    /**
     * 返回当前运行毫秒（非请求毫秒）
     * @return float
     * @copyright 魔网天创信息科技
     * @author    ComingDemon
     */
    public function mstime()
    {
        [$s1, $s2] = explode(' ', microtime());

        return (int)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
     * 毫秒时间戳转换日期格式
     *
     * @param     $format
     * @param int $mstime
     *
     * @return false|mixed|string
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function msdate($format, $mstime = DEMON_MSTIME)
    {
        //  除以一千取出秒部分和毫秒部分
        $info = explode('.', (int)$mstime / 1000);
        //  将所有反斜杠的f变成其他字符串临时保存
        if (strpos($format, '\f') !== false) {
            $format = str_replace('\\f', '{@f}', $format);
            $cute = true;
        }
        //  将秒部分进行正常格式化
        $date = date($format, $info[0]);
        $info[1] = isset($info[1]) ? $info[1] : 0;
        //  如果包含小写f，则替换f为毫秒部分（自动补齐为3位数）
        if (strpos($date, 'f') !== false)
            $date = str_replace('f', self::strFill(3, $info[1], 'right'), $date);
        //  将临时字符串还原为f
        if ($cute ?? false)
            $date = str_replace('{@f}', 'f', $date);

        //  返回秒部分格式化结果
        return $date;
    }

    /**
     * 通过毫秒格式获取时间戳
     *
     * @param     $msdate
     * @param int $time
     *
     * @return string
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function msdateToTime($msdate, $time = DEMON_TIME)
    {
        [$usec, $sec] = explode('.', $msdate);
        $date = strtotime($usec, $time);

        return str_pad($date . $sec, 13, '0', STR_PAD_RIGHT);
    }

    /**
     * 根据规则获取一个时间戳
     *
     * @param        $rule   //内置strtotime规则（）
     * @param mixed  $format //如果要获取特殊的规则
     * @param mixed  $time   //起点时间戳
     * @param string $type   //mstime表示毫秒，默认为time秒
     *
     * @return false|int
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function timeBuild($rule, $format = 'Y-m-d H:i:s', $time = DEMON_MSTIME, $type = 'mstime')
    {
        $ratio = 1;
        switch ($type) {
            case 'mstime':
                $ratio = 1000;
                break;
            case 'date':
                $time = strtotime($time);
                break;
        }

        return strtotime(date($format, strtotime($rule, $time / $ratio))) * $ratio;
    }

    /**
     * 合并加密2个字符串并且MD5加密
     *
     * @param        $string //目标字符串
     * @param string $salt   //盐值
     *
     * @return string
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function sme($string, $salt = '')
    {
        if (!$string)
            return '';
        $newsalt = $salt ? md5($salt) : $salt;
        $n = 0;
        for ($i = 0; $i < 32; $i++)
            $n += ord($newsalt[$i]);
        $m = 0;
        for ($j = 0; $j < 6; $j++)
            $m += ord(!empty($salt[$j]) ? $salt[$j] : '');
        $salt = $n * (int)$m;
        $string = md5(md5($string) . $salt);

        return $string;
    }

    /**
     * 字符串加密（异或加密，需要验证密钥和次数）
     *
     * @param        $string //目标字符串
     * @param        $type   //类型（0：解密，1：加密）
     * @param string $key    //密钥
     * @param int    $num    //加密次数
     *
     * @return mixed|string
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function sse($string, $type = 1, $key = '', $num = 2)
    {
        if (!$string)
            return '';
        if (!$key)
            $key = 'demon';
        //  将问号提前转义，解密的时候注意还原
        $string = str_replace('?', '<<\>>', $string);
        $key = self::sme((string)$key, 'num' . $num);
        $key_length = strlen($key);
        $string = $type == 0 ? base64_decode($string) : substr($key, 8, 8) . $string . substr($key, 16, 8);
        $string_length = strlen($string);
        $rndkey = $box = [];
        $result = '';
        for ($i = 0; $i < $num; $i++) {
            $rndkey[$i] = ord($key[$i % $key_length]);
            $box[$i] = $i;
        }
        for ($j = $i = 0; $i < $num; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % $num;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % $num;
            $j = ($j + $box[$a]) % $num;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % $num]));
        }
        if ($type != 0) {
            $result = base64_encode($result);

            return $result;
        }
        else return substr($result, 0, 8) == substr($key, 8, 8) && substr($result, -8, 8) == substr($key, 16, 8) ? str_replace('<<\>>', '?', substr(substr($result, 8), 0, -8)) : '';
    }

    /**
     * 令牌生成（可以用做邀请码或其他内容，如果需要解码则需要相同的种子和补位规则）
     *
     * @param        $content
     * @param int    $type   类型
     * @param string $seed   种子
     * @param string $seam   补位字符
     * @param int    $length 总长度
     *
     * @return float|int|string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function token($content, $type = 1, $seed = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', $seam = '0', $length = 6)
    {
        //  计算种子长度来锚定进制精度
        $scale = mb_strlen($seed);
        //  解码
        if ($type != 1) {
            if (mb_strrpos($content, $seam) !== false)
                $code = mb_substr($content, mb_strrpos($content, $seam) + 1);
            $content = self::strSplit(self::strRev($content));
            $num = 0;
            for ($i = 0; $i < count($content); $i++)
                $num += mb_strpos($seed, $content[$i]) * pow($scale, $i);

            return $num;
        }
        //  加码
        else {
            $code = '';
            $seed = self::strSplit($seed);
            while ($content > 0) {
                $mod = $content % $scale;
                $content = ($content - $mod) / $scale;
                $code = $seed[$mod] . $code;
            }

            return self::strFill($length, $code, STR_PAD_LEFT, $seam);
        }
    }

    /**
     * 数据错误生成（提供给验证数据标准性使用）
     *
     * @param int    $code
     * @param string $message
     *
     * @return string
     * @copyright 魔网天创信息科技
     * @author    ComingDemon
     */
    public function errorBuild($code = DEMON_CODE_FAIL, $message = ''):string
    {
        return "error|{$code}" . ($message ? "|{$message}" : '');
    }

    /**
     * 验证数据标准性（是否为错误信息）
     *
     * @param      $info //返回信息
     * @param      $func //此处定义回调方法
     *
     * @return mixed
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function errorCheck($info, $func = null)
    {
        //  特殊判断
        $error = 'error|';
        if (gettype($info) == 'string' && mb_strpos($info, $error) === 0) {
            $data = mb_substr($info, mb_strlen($error));
            //  判断剩下的文字是否是数字开头并且竖线分隔
            $code = mb_substr($data, 0, mb_strpos($data, '|'));
            if (is_numeric($code)) {
                $code = (int)$code;
                $message = mb_substr($data, mb_strpos($data, '|') + 1);
            }
            else {
                if (is_numeric($data)) {
                    $code = (int)$data;
                    $message = '';
                }
                else {
                    $code = DEMON_CODE_PARAM;
                    $message = $data;
                }
            }

            return $func ? $func(self::arrayToObject(['code' => $code, 'message' => $message])) : false;
        }
        else if (is_numeric($info) && $info > 0)
            return $func ? $func(self::arrayToObject(['code' => (int)$info])) : false;

        return $info;
    }

    /**
     * 当前是否SSL
     * @return bool
     * @copyright 魔网天创信息科技
     * @author    ComingDemon
     */
    public function isSsl()
    {
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == '1' || strtolower($_SERVER['HTTPS']) == 'on'))
            return true;
        else if (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https')
            return true;
        else if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '43')
            return true;
        else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO'])
            return true;

        return false;
    }

    /**
     * 检测是否使用手机访问
     * @return bool
     * @copyright 魔网天创信息科技
     * @author    ComingDemon
     */
    public function isMobile()
    {
        if (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], 'wap'))
            return true;
        else if (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtoupper($_SERVER['HTTP_ACCEPT']), 'VND.WAP.WML'))
            return true;
        else if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE']))
            return true;
        else if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT']))
            return true;
        else return false;
    }

    /**
     * 检查浏览器版本信息
     *
     * @param null $agent
     *
     * @return string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function browser($agent = null)
    {
        //  获取浏览器信息
        if (!$agent && empty($_SERVER['HTTP_USER_AGENT']))
            return 'unknown';
        $agent = $agent ? : $_SERVER['HTTP_USER_AGENT'];
        $mobileAgent = strtolower($agent);
        //  各类浏览器等内容的标识
        static $IPhoneList = ['iphone'];
        static $IPadList = ['ipad'];
        static $WinPhoneList = ['windows phone'];
        static $WmlList = [
            'cect', 'compal', 'ctl', 'lg', 'nec', 'tcl', 'alcatel',
            'ericsson', 'bird', 'daxian', 'dbtel', 'eastcom', 'pantech',
            'dopod', 'philips', 'haier', 'konka', 'kejian', 'lenovo',
            'benq', 'mot', 'soutec', 'nokia', 'sagem', 'sgh', 'sed',
            'capitel', 'panasonic', 'sonyericsson', 'sharp', 'amoi', 'panda', 'zte'
        ];
        static $TouchList = [
            'android', 'phone', 'mobile', 'wap', 'netfront', 'java', 'opera mobi',
            'opera mini', 'ucweb', 'windows ce', 'symbian', 'series', 'webos', 'sony',
            'blackberry', 'dopod', 'nokia', 'samsung', 'palmsource', 'xda', 'pieplus',
            'meizu', 'midp', 'cldc', 'motorola', 'foma', 'docomo', 'up.browser',
            'up.link', 'blazer', 'helio', 'hosin', 'huawei', 'novarra', 'coolpad',
            'webos', 'techfaith', 'palmsource', 'alcatel', 'amoi', 'ktouch',
            'nexian', 'ericsson', 'philips', 'sagem', 'wellcom', 'bunjalloo',
            'maui', 'smartphone', 'iemobile', 'spice', 'bird', 'zte-', 'longcos',
            'pantech', 'gionee', 'portalmmm', 'jig browser', 'hiptop', 'benq',
            'haier', '^lct', '320x320', '240x320', '176x220'
        ];
        static $weixinList = ['micromessenger'];
        if (self::arrayStrpos($mobileAgent, $weixinList))
            return 'wechat';
        if (self::arrayStrpos($mobileAgent, $IPhoneList))
            return 'iphone';
        else if (self::arrayStrpos($mobileAgent, $IPadList))
            return 'ipad';
        else if (self::arrayStrpos($mobileAgent, $WinPhoneList))
            return 'wphone';
        else if (self::arrayStrpos($mobileAgent, $WmlList))
            return 'wml';
        else if (self::arrayStrpos($mobileAgent, $TouchList))
            return 'touch';
        else {
            if (stripos($agent, 'MSIE') !== false || stripos($agent, 'rv:11.0'))
                return 'ie';
            else if (stripos($agent, 'Edge') !== false)
                return 'edge';
            else if (stripos($agent, 'Firefox') !== false)
                return 'firefox';
            else if (stripos($agent, 'Chrome') !== false)
                return 'chrome';
            else if (stripos($agent, 'Opera') !== false)
                return 'opera';
            else if ((stripos($agent, 'Chrome') == false) && stripos($agent, 'Safari') !== false)
                return 'safari';
            else return 'other';
        }
    }

    /**
     * 获取客户端IP地址
     *
     * @param bool $adv  //是否进行高级模式获取(有可能被伪装,通过配置nginx等外部方案解决)
     * @param int  $type //返回类型(0:返回IP地址,1:返回IPV4地址数字)
     *
     * @return mixed
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function ip($adv = true, $type = 0)
    {
        $type = $type ? 1 : 0;
        static $ip = null;
        if ($ip !== null)
            return $ip[$type];
        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if ($pos !== false)
                    unset($arr[$pos]);
                if (!count($arr))
                    $ip = $_SERVER['REMOTE_ADDR'];
                else $ip = trim(current($arr));
            }
            else if (isset($_SERVER['HTTP_CLIENT_IP']))
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            else if (isset($_SERVER['REMOTE_ADDR']))
                $ip = $_SERVER['REMOTE_ADDR'];
        }
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ip = $_SERVER['REMOTE_ADDR'];

        // IP地址合法验证
        $long = sprintf('%u', ip2long($ip));
        $ip = $long ? [$ip, $long] : ['0.0.0.0', 0];

        return $ip[$type];
    }

    /**
     * 通过表达式获取IP区间
     *
     * @param string $expression
     * @param bool   $int
     *
     * @return array
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function ipRange($expression, $int = false)
    {
        //  默认赋值
        $range = [$expression, $expression];
        //  是CIDR模式
        if (strstr($expression, '/')) {
            $cidr = explode('/', $expression);
            $range[0] = long2ip((ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1]))));
            $range[1] = long2ip((ip2long($range[0])) + pow(2, (32 - (int)$cidr[1])) - 1);
        }
        //  循环处理补位和通配
        else {
            foreach ($range as $key => $val) {
                $val = self::strReplacement('#0.#1.#2.#3', explode('.', $val) + array_fill(0, 4, '*'), ['#', '']);
                $range[$key] = str_replace('*', $key ? '255' : '0', $val);
            }
        }

        //  返回范围
        return !$int ? $range : [ip2long($range[0]), ip2long($range[1])];
    }

    /**
     * 检查IP区间
     *
     * @param string[]|string $expression
     * @param string          $type
     * @param null            $ip
     *
     * @return bool
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function ipCheck($expression, $type = 'assert', $ip = null)
    {
        $ip = $ip ? : self::ip();
        $expression = !is_array($expression) ? [$expression] : $expression;
        foreach ($expression as $exp) {
            [$min, $max] = self::ipRange($exp, true);
            if ((ip2long($ip) < $min || ip2long($ip) > $max)) {
                if ($type == 'assert')
                    return false;
            }
            else {
                if ($type == 'match')
                    return true;
            }
        }

        return $type == 'assert';
    }

    /**
     * 获取MD5
     *
     * @param      $content
     * @param bool $cut
     * @param bool $int
     *
     * @return false|string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function md5($content, $cut = true, $int = false)
    {
        //  获取MD5
        $md5 = !$cut ? md5($content) : substr(md5($content), 8, 16);

        //  返回类型
        return !$int ? $md5 : base_convert($md5, 16, 10);
    }

    /**
     * 通用正则验证
     *
     * @param $content
     * @param $rule
     * @param $func
     *
     * @return bool
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function regexp($content, $rule, $func = null)
    {
        $rules = [
            //  布尔
            'bool' => '/^[0-1]$/',
            //  正整数
            'integer' => '/^[0-9]*[1-9][0-9]*$/',
            //  数字
            'number' => '/^\d+$/',
            //  正数
            'plus' => '/^(([0-9]+\.[0-9]*[1-9][0-9]*)|([0-9]*[1-9][0-9]*\.[0-9]+)|([0-9]*[1-9][0-9]*))$/',
            //  邮箱
            'email' => '/^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$/',
            //  手机
            'mobile' => '/^1([34578][0-9]|6[2567]|9[1589])[0-9]{8}$/',
            //  链接
            'url' => '/^http(s)?:\\/\\/.+/',
            //  QQ
            'qq' => '/^[1-9][0-9]{4,11}$/',
            //  空
            'null' => '/\s/g',
            //  IP
            'ip' => '/^((?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1 -9]?\d))))$/',
            //  纯中文
            'chs' => '/^[\x{4e00}-\x{9fa5}]+$/u',
            //  不允许特殊符号
            'nosym' => '/^(?![0-9])[a-zA-Z0-9\x{4e00}-\x{9fa5}]+$/u',
            //  身份证
            'id' => '/^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/',
            //  姓名
            'name' => '/^([\x{4e00}-\x{9fa5}\·]{2,7}|[a-zA-Z\.\s]{0,0})+$/u',
            //  32位MD5
            'md5' => '/^([a-fA-F0-9]{32})$/',
            //  BASE64编码
            'base64' => '/^(data:(.*?)base64,)/',
            //  16进制编码
            'hex' => '/^[a-fA-F0-9xX][a-fA-F0-9xX][a-fA-F0-9]+$/',
        ];
        //  错误回调
        $result = function($result) use ($content, $func) { return !$result ? ($func ? $func($content) : false) : $result; };
        if ($rule == 'json') {
            if (!is_string($content))
                return $result(false);
            $content = json_decode($content);
            if (!$content || !is_object($content) && !is_array($content))
                return $result(false);

            return json_last_error() == JSON_ERROR_NONE;
        }
        else if (isset($rules[$rule]))
            return $result(preg_match($rules[$rule], $content));
        else return $result(preg_match($rule, $content));
    }

    /**
     * 当前的请求类型
     *
     * @param bool $origin //获取原始请求类型
     *
     * @return  mixed
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function requestMethod($origin = false)
    {
        return !$origin && isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) ? strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) : (PHP_SAPI == 'cli' ? 'GET' : ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    }

    /**
     * 获取当前包含协议的域名
     *
     * @param bool $protocol //是否包含协议
     *
     * @return string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function requestDomain($protocol = false)
    {
        return $protocol ? (self::isSsl() ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') : ($_SERVER['HTTP_HOST'] ?? '');
    }

    /**
     * 获取当前URL 不含QUERY_STRING
     *
     * @param int $type // 域名显示（1：域名，2：协议）
     *
     * @return string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function requestBase($type = 0)
    {
        $str = self::requestUrl();
        $base = strpos($str, '?') ? strstr($str, '?', true) : $str;

        return $type ? self::requestDomain($type == 2) . $base : $base;
    }

    /**
     * 获取当前完整URL 包括QUERY_STRING
     *
     * @param int $type // 域名显示（1：域名，2：协议）
     *
     * @return string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function requestUrl($type = 0)
    {
        if (PHP_SAPI == 'cli')
            $url = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
        else if (isset($_SERVER['HTTP_X_REWRITE_URL']))
            $url = $_SERVER['HTTP_X_REWRITE_URL'];
        else if (isset($_SERVER['REQUEST_URI']))
            $url = $_SERVER['REQUEST_URI'];
        else if (isset($_SERVER['ORIG_PATH_INFO']))
            $url = $_SERVER['ORIG_PATH_INFO'] . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
        else $url = '';

        return $type ? self::requestDomain($type == 2) . $url : $url;
    }

    /**
     * 发起CURL请求
     *
     * @param        $url
     * @param string $type
     * @param array  $parm
     * @param string $dataType
     * @param string $ua
     *
     * @return bool|mixed
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function curl($url, $parm = [], $config = [], $func = null)
    {
        //  请求类型
        $config['method'] = strtolower($config['method'] ?? 'get');
        //  定义参数
        $config['dataType'] = $config['dataType'] ?? 'json';
        //  定义代理头
        $config['userAgent'] = $config['userAgent'] ?? 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.75 Safari/537.36';
        //  预请求内容
        $config['optionFunction'] = $config['optionFunction'] ?? null;
        // 初始化CURL
        $curl = curl_init();
        //  如果Curl支持IPv4，则设置Curl默认访问为IPv4，可解决超时问题
        if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4'))
            curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        //  设置Curl请求连接时的最长秒数，如果设置为0，则无限
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 120);
        //  设置Curl总执行动作的最长秒数，如果设置为0，则无限
        curl_setopt($curl, CURLOPT_TIMEOUT, 1200);
        // 请求类型
        switch ($config['method']) {
            // 获取外链文件
            case 'file':
                // HTTPS特殊处理
                if (stripos($url, 'https://') !== false) {
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($curl, CURLOPT_SSLVERSION, 1);
                }
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_USERAGENT, $config['userAgent']);
                curl_setopt($curl, CURLOPT_REFERER, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                //  请求前
                if ($config['optionFunction'] && is_object($config['optionFunction']))
                    $config['optionFunction'] ($curl);
                $result = curl_exec($curl);
                $status = curl_getinfo($curl);
                curl_close($curl);
                //  请求后
                if ($func && self::isFunction($func))
                    return call_user_func($func, $result, $status);

                return isset($status['http_code']) && $status['http_code'] == 200 ? $result : false;
                break;
            // GET请求|POST请求
            case 'get':
            case 'post':
                // 如果是GET请求的话
                if ($config['method'] == 'get') {
                    //  拼接URL参数
                    if ($parm) {
                        $url .= stripos($url, '?') === false ? '?' : '&';
                        if (!is_string($parm)) {
                            foreach ($parm as &$val)
                                $val = urlencode($val);
                            $parm = http_build_query($parm);
                        }
                        $url .= $parm;
                    }
                    //  允许重定向
                    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                }
                else {
                    //  定义POST
                    curl_setopt($curl, CURLOPT_POST, true);
                    //  定义POST内容
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $parm);
                }
                // HTTPS特殊处理
                if (stripos($url, 'https://') !== false) {
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($curl, CURLOPT_SSLVERSION, 1);
                }
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_USERAGENT, $config['userAgent']);
                curl_setopt($curl, CURLOPT_REFERER, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                //  请求前
                if ($config['optionFunction'] && is_object($config['optionFunction']))
                    $config['optionFunction'] ($curl);
                $result = curl_exec($curl);
                $status = curl_getinfo($curl);
                curl_close($curl);
                //  请求后
                if ($func && self::isFunction($func))
                    return call_user_func($func, $result, $status);
                //  如果成功返回内容并且状态码为200表示成功
                if (isset($status['http_code']) && $status['http_code'] == 200)
                    return $config['dataType'] == 'json' ? json_decode($result) : $result;
                //  如果存在状态码表示访问成功但结果错误
                else if (isset($status['http_code']))
                    return $status['http_code'];
                //  访问不成功
                else return false;
                break;
        }

        return false;
    }

    /**
     * 获取变量 支持过滤和默认值
     *
     * @param array             $data    //数据源
     * @param false|string      $name    //字段名
     * @param mixed|null        $default //默认值
     * @param array|string|null $filter  //过滤函数
     *
     * @return array|mixed
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function arguer($data = [], $name = '', $default = null, $filter = null)
    {
        //  如果data是JSON数据，则转换为数组
        if (is_string($data)) {
            $tempData = json_decode($data, true);
            $data = !is_null($tempData) ? $tempData : $data;
        }
        //  如果data是对象，则转换为数组
        if (is_object($data))
            $data = json_decode(json_encode($data), true);
        // 获取原始数据
        if ($name === false)
            return $data;
        $name = trim((string)$name);
        if ($name != '') {
            // 按.拆分成多维数组进行判断
            foreach (explode('.', $name) as $val) {
                if (isset($data[$val]))
                    $data = $data[$val];
                else if (is_string($data)) {
                    $data = json_decode($data, true);
                    if (isset($data[$val]) && $data[$val])
                        $data = $data[$val];
                    else
                        return $default;
                }
                else if (is_object($data)) {
                    $data = (array)$data;
                    if (isset($data[$val]))
                        $data = $data[$val];
                    else return $default;
                }
                // 无输入数据，返回默认值
                else return $default;
            }
        }

        //  如果是数组或者对象
        if (is_object($data) || is_array($data))
            return self::arrayCast($data, $filter);
        // 强制类型转换和过滤
        else if ($data !== $default)
            return self::typeCast($data, $filter);

        return $data;
    }

    /**
     * 设置获取获取REQUEST参数
     *
     * @param array|string $name    //变量名
     * @param mixed|null   $default //默认值
     * @param array|string $filter  //过滤方法
     *
     * @return array|mixed|string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function input($name = '', $default = null, $filter = '')
    {
        //  从Input中获取信息
        if ($raw = file_get_contents('php://input')) {
            //  如果是JSON则合并到$_REQUEST中
            if (self::regexp($raw, 'json') && $raw = json_decode($raw, true))
                $_REQUEST = array_merge($_REQUEST, $raw);
        }
        $request = $_REQUEST;
        if (is_array($name))
            $request = array_merge($request, $name);

        return self::arguer($request, $name, $default, $filter);
    }

    /**
     * 数组递归转换
     *
     * @param $array
     * @param $type
     *
     * @return mixed
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function arrayCast($array, $type)
    {
        $isObject = false;
        if (is_object($array)) {
            $isObject = true;
            $array = self::objectToArray($array);
        }

        $array = self::_arrayCast($array, $type);
        if ($isObject)
            $array = self::arrayToObject($array);

        return $array;
    }

    private function _arrayCast($array, $type)
    {
        foreach ($array as $key => $val)
            $array[$key] = !is_array($val) ? self::typeCast($val, $type) : self::_arrayCast($val, $type);

        return $array;
    }

    /**
     * 强制类型转换
     *
     * @param string $data
     * @param string $type
     *
     * @return string
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function typeCast($data, $type)
    {
        //  如果是对象函数则直接在体系内自定义
        if (self::isFunction($type, false))
            return call_user_func($type, $data);
        //  如果不是字符串则直接返回
        if (!is_string($type))
            return $data;
        //  根据类型进行转换
        switch (strtolower($type)) {
            //  双精度
            case 'double':
                $data = (double)$data;
                break;
            //  数字
            case 'int':
                $data = (int)$data;
                break;
            //  浮点
            case 'float':
                $data = (float)$data;
                break;
            //  布尔
            case 'bool':
                $data = ((is_string($data) && $data == 'false') || $data == false || $data == null || $data == '') ? false : (boolean)$data;
                break;
            //  状态
            case 'status':
                $data = (boolean)($data == 'on');
                break;
            //  绝对值
            case 'abs':
                $data = abs($data);
                break;
            //  首字母大写
            case 'uc':
                $data = ucfirst((string)$data);
                break;
            //  首字母小写
            case 'lc':
                $data = lcfirst((string)$data);
                break;
            //  全大写
            case 'upper':
                $data = strtoupper((string)$data);
                break;
            //  全小写
            case 'lower':
                $data = strtolower((string)$data);
                break;
            //  防止xss
            case 'xss':
                $data = str_replace('_x000D_', '', (new AntiXSS())->xss_clean((string)$data));
                break;
            //  防止xss但允许部分HTML标签和STYLE样式
            case 'xssh':
                //  转换事件
                $events = [];
                foreach (self::xss('onEvent') as $event)
                    $events[] = "/{$event}/si";
                $data = preg_replace_callback($events, function($match) { return preg_replace("/on/si", '<span>$0$1</span>', $match[0]); }, (string)$data);
                //  转换脚本
                $calls = [];
                foreach (self::xss('call') as $call)
                    $calls[] = "/{$call}\:/si";
                $data = preg_replace_callback($calls, function($match) { return preg_replace("/\:/si", '<span>$0</span>', $match[0]); }, (string)$data);
                //  调用XSS过滤
                $data = str_replace('_x000D_', '', (new AntiXSS())->removeEvilHtmlTags(['iframe', 'audio', 'video', 'source'])->removeEvilAttributes(['style'])->xss_clean((string)$data));
                break;
            // 将HTML标签实体化
            case 'entity':
                $data = htmlspecialchars((string)$data);
                break;
            //  过滤空格
            case 'trim':
                $data = preg_replace('/\s+/', '', (string)$data);
                break;
            //  字符串
            case 'string':
            default:
                $data = (string)$data;
        }

        return $data;
    }

    /**
     * xss
     *
     * @param null $type
     *
     * @return string[]|AntiXSS
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function xss($type = null)
    {
        switch ($type) {
            case 'onEvent':
                return [
                    'onAbort', 'onActivate', 'onAttribute', 'onAfterPrint', 'onAfterScriptExecute', 'onAfterUpdate', 'onAnimationCancel', 'onAnimationEnd', 'onAnimationIteration', 'onAnimationStart', 'onAriaRequest', 'onAutoComplete', 'onAutoCompleteError', 'onAuxClick',
                    'onBeforeActivate', 'onBeforeCopy', 'onBeforeCut', 'onBeforeDeactivate', 'onBeforeEditFocus', 'onBeforePaste', 'onBeforePrint', 'onBeforeScriptExecute', 'onBeforeUnload', 'onBeforeUpdate', 'onBegin', 'onBlur', 'onBounce', 'onCancel',
                    'onCanPlay', 'onCanPlayThrough', 'onCellChange', 'onChange', 'onClick', 'onClose', 'onCommand', 'onCompassNeedsCalibration', 'onContextMenu', 'onControlSelect', 'onCopy', 'onCueChange', 'onCut',
                    'onDataAvailable', 'onDataSetChanged', 'onDataSetComplete', 'onDblClick', 'onDeactivate', 'onDeviceLight', 'onDeviceMotion', 'onDeviceOrientation', 'onDeviceProximity', 'onDrag', 'onDragDrop', 'onDragEnd', 'onDragEnter', 'onDragLeave', 'onDragOver', 'onDragStart', 'onDrop', 'onDurationChange',
                    'onEmptied', 'onEnd', 'onEnded', 'onError', 'onErrorUpdate', 'onExit',
                    'onFilterChange', 'onFinish', 'onFocus', 'onFocusIn', 'onFocusOut', 'onFormChange', 'onFormInput', 'onFullScreenChange', 'onFullScreenError', 'onGotPointerCapture',
                    'onHashChange', 'onHelp',
                    'onInput', 'onInvalid',
                    'onKeyDown', 'onKeyPress', 'onKeyUp',
                    'onLanguageChange', 'onLayoutComplete', 'onLoad', 'onLoadedData', 'onLoadedMetaData', 'onLoadStart', 'onLoseCapture', 'onLostPointerCapture',
                    'onMediaComplete', 'onMediaError', 'onMessage', 'onMouseDown', 'onMouseEnter', 'onMouseLeave', 'onMouseMove', 'onMouseOut', 'onMouseOver', 'onMouseUp', 'onMouseWheel', 'onMove', 'onMoveEnd', 'onMoveStart', 'onMozFullScreenChange', 'onMozFullScreenError', 'onMozPointerLockChange', 'onMozPointerLockError', 'onMsContentZoom',
                    'onMsFullScreenChange', 'onMsFullScreenError', 'onMsGestureChange', 'onMsGestureDoubleTap', 'onMsGestureEnd', 'onMsGestureHold', 'onMsGestureStart', 'onMsGestureTap', 'onMsGotPointerCapture', 'onMsInertiaStart', 'onMsLostPointerCapture', 'onMsManipulationStateChanged', 'onMsPointerCancel', 'onMsPointerDown', 'onMsPointerEnter',
                    'onMsPointerLeave', 'onMsPointerMove', 'onMsPointerOut', 'onMsPointerOver', 'onMsPointerUp', 'onMsSiteModeJumpListItemRemoved', 'onMsThumbnailClick',
                    'onOffline', 'onOnline', 'onOutOfSync', 'onPage', 'onPageHide', 'onPageShow', 'onPaste', 'onPause', 'onPlay', 'onPlaying', 'onPointerCancel', 'onPointerDown', 'onPointerEnter', 'onPointerLeave', 'onPointerLockChange', 'onPointerLockError', 'onPointerMove', 'onPointerOut', 'onPointerOver', 'onPointerUp', 'onPopState', 'onProgress',
                    'onPropertyChange',
                    'onqt_error',
                    'onRateChange', 'onReadyStateChange', 'onReceived', 'onRepeat', 'onReset', 'onResize', 'onResizeEnd', 'onResizeStart', 'onResume', 'onReverse', 'onRowDelete', 'onRowEnter', 'onRowExit', 'onRowInserted', 'onRowsDelete', 'onRowsEnter', 'onRowsExit', 'onRowsInserted',
                    'onScroll', 'onSearch', 'onSeek', 'onSeeked', 'onSeeking', 'onSelect', 'onSelectionChange', 'onSelectStart', 'onStalled', 'onStorage', 'onStorageCommit', 'onStart', 'onStop', 'onShow', 'onSyncRestored', 'onSubmit', 'onSuspend', 'onSynchRestored',
                    'onTimeError', 'onTimeUpdate', 'onTimer', 'onTrackChange', 'onTransitionEnd', 'onToggle', 'onTouchCancel', 'onTouchEnd', 'onTouchLeave', 'onTouchMove', 'onTouchStart', 'onTransitionCancel', 'onTransitionEnd',
                    'onUnload', 'onURLFlip', 'onUserProximity',
                    'onVolumeChange',
                    'onWaiting', 'onWebKitAnimationEnd', 'onWebKitAnimationIteration', 'onWebKitAnimationStart', 'onWebKitFullScreenChange', 'onWebKitFullScreenError', 'onWebKitTransitionEnd', 'onWheel',
                ];
                break;
            case 'call':
                return [
                    'javascript',
                    'jar',
                    'applescript',
                    'vbscript',
                    'vbs',
                    'wscript',
                    'jscript',
                    'behavior',
                    'mocha',
                    'livescript',
                    'view-source',
                ];
                break;
            default:
                return new AntiXSS();
                break;
        }
    }

    /**
     * 创建目录
     *
     * @param $dir
     *
     * @return bool
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function dirMake($dir)
    {
        if (!is_dir($dir)) {
            if (!self::dirMake(dirname($dir)))
                return false;
            if (!mkdir($dir))
                return false;
        }

        return true;
    }

    /**
     * 复制目录
     *
     * @param $source
     * @param $target
     *
     * @return bool
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function dirCopy($source, $target)
    {
        if (!file_exists($source))
            return false;
        if (!file_exists($target))
            self::dirMake($target);
        $dir = opendir($source);
        while ($fileName = readdir($dir)) {
            if ($fileName == '.' || $fileName == '..')
                continue;
            $sourceFilePath = $source . DIRECTORY_SEPARATOR . $fileName;
            $targetFilePath = $target . DIRECTORY_SEPARATOR . $fileName;
            if (is_file($sourceFilePath))
                copy($sourceFilePath, $targetFilePath);
            else self::dirCopy($sourceFilePath, $targetFilePath);
        }
        closedir($dir);

        return true;
    }

    /**
     * 清空目录
     *
     * @param      $dir
     * @param bool $remove
     *
     * @return bool
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function dirClear($dir, $remove = false)
    {
        $op = opendir($dir);
        while ($item = readdir($op)) {
            if ($item == '.' || $item == '..')
                continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path))
                self::dirClear($path, true);
            else unlink($path);
        }
        closedir($op);

        return $remove ? rmdir($dir) : true;
    }

    /**
     * 获取目录中所有文件
     *
     * @param $dir
     *
     * @return array
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function dirList($dir)
    {
        $handle = opendir($dir);
        //定义用于存储文件名的数组
        $array_file = [];
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..' && self::suffix($file))
                $array_file[] = $file;
        }
        closedir($handle);

        return $array_file;
    }

    /**
     * 创建一个文件
     *
     * @param        $file    //文件名称（包含完整后缀）
     * @param string $content //文件内容
     * @param        $dir     //文件目录（为空时全部读取文件名称）
     * @param        $type    //内容类型（比如base64）
     *
     * @return bool
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function fileCreate($file, $content = '', $dir = '', $type = null)
    {
        //  如果是文件夹
        if ($dir) {
            //  创建文件夹
            self::dirMake($dir);
            //  打开文件
            $fopen = fopen($dir . DIRECTORY_SEPARATOR . $file, 'w');
        }
        else $fopen = fopen($file, 'w');

        //  内容类型
        switch ($type) {
            //  BASE64编码格式
            case 'base64':
                $result = self::regexp($content, 'base64');
                if (!$result)
                    return false;
                $content = base64_decode(explode(',', $content)[1]);
                break;
        }

        //  写入文件
        fwrite($fopen, $content);
        //  关闭文件
        fclose($fopen);

        return $dir . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * 从服务器下载文件
     *
     * @param $file  //文件名称
     * @param $name  //下载名称（如果设置名称，则直接返回二进制内容）
     * @param $speed //下载限速
     *
     * @return mixed
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function fileDownload($file, $name = false, $speed = 0)
    {
        $fileType = strtolower(strstr($file, '.'));
        $fileDir = fopen($file, "r");
        $fileSize = filesize($file) + 1024;
        if ($name) {
            header("Content-type: application/octet-stream");
            header("Accept-Ranges: bytes");
            header("Accept-Length: " . $fileSize);
            header("Content-Disposition: attachment; filename=" . $name . $fileType);
            if ($speed) {
                ob_end_clean();
                ob_implicit_flush();
                header("X-Accel-Buffering: no");
                $count = 0;
                while ($fileSize - $count > 0) {
                    echo fread($fileDir, $speed);
                    $count += $speed;
                    flush();
                    sleep(1);
                }
            }
            else {
                echo fread($fileDir, filesize($file));
                fclose($fileDir);
            }
        }
        else {
            $content = fread($fileDir, filesize($file));
            fclose($fileDir);

            return $content;
        }
    }

    /**
     * 上传文件到服务器（文件信息[$_FILE]，存放位置，存放文件名）
     *
     * @param $file_info
     * @param $src_dir
     * @param $src_file
     * @param $parm
     *
     * @return mixed
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function fileUpload($file_info = [], $src_dir, $src_file, $parm = [])
    {
        //  分析附加参数
        $file_format = $parm['format'] ?? [];   //文件格式，数组['jpg','gif','png']
        $file_size = $parm['size'] ?? [0, 0]; //文件尺寸，数组[min,max]
        $isMobile = self::isMobile() && is_string($file_info);
        //  移动版的话需要特殊处理
        if ($isMobile) {
            //  判断文件是否存在
            $file_info = strtoupper('http_' . $file_info);
            if (!isset($_SERVER[$file_info]))
                return DEMON_CODE_NONE;
            //  取截图和文件的基本信息
            $fileData = file_get_contents('php://input');
            $fileFormat = self::suffix($_SERVER['CONTENT_TYPE'], '/');
            $fileSize = strlen($fileData) / 1024;
        }
        else {
            //  判断文件是否存在
            if (!isset($file_info))
                return DEMON_CODE_NONE;
            //  取截图和文件的基本信息
            $fileName = $file_info['name'];
            $fileFormat = self::suffix($fileName);
            $fileSize = $file_info['size'] / 1024;
        }
        //  判断文件格式（返回1则表示格式不正确）
        if ($file_format && !in_array($fileFormat, $file_format))
            return DEMON_CODE_MEDIA;
        //  判断文件尺寸（返回406则表示尺寸过小，返回413则表示尺寸过大）
        if ($file_size) {
            if ($file_size[0] && $fileSize < $file_size[0])
                return DEMON_CODE_FAIL;
            if ($file_size[1] && $fileSize > $file_size[1])
                return DEMON_CODE_LARGE;
        }
        //  判断文件存放目录（返回4则表示无法创建目录）
        if (!is_dir($src_dir)) {
            $status = self::dirMake($src_dir);
            if (!$status)
                return DEMON_CODE_SERVER;
        }
        //  移动端特殊处理和普通处理
        $status = $isMobile ? file_put_contents($src_dir . '/' . $src_file, $fileData) : move_uploaded_file($file_info['tmp_name'], $src_dir . '/' . $src_file);
        if (!$status)
            return DEMON_CODE_SERVER;

        //  返回最终文件地址
        return ['file' => $src_dir . '/' . $src_file];
    }

    /**
     * 获取后缀
     *
     * @param $content //名称
     * @param $tag     //分隔符
     *
     * @return string
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function suffix($content, $tag = '.')
    {
        return strtolower(trim(substr(strrchr($content, $tag), 1, 10)));
    }

    /**
     * 获取图片类型列表
     * @return array
     * @copyright 魔网天创信息科技
     * @author    ComingDemon
     */
    private function _imageType()
    {
        return [1 => 'gif', 2 => 'jpg', 3 => 'png', 4 => 'swf', 5 => 'psd', 6 => 'bmp', 7 => 'tiff', 8 => 'tiff', 9 => 'jpc', 10 => 'jp2', 11 => 'jpx', 12 => 'jb2', 13 => 'swc', 14 => 'iff', 15 => 'wbmp', 16 => 'xbm', 17 => 'webp', 18 => 'jpeg'];
    }

    /**
     * 获取图片对象
     *
     * @param $mode
     * @param $src_img
     * @param $parm
     *
     * @return mixed
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function imageObject($mode = 'read', $src_img, $parm = [])
    {
        //  模式
        switch ($mode) {
            //  写入文件
            case 'write':
                $object = $parm['object'] ?? null;
                $type = self::arguer('type', 'jpg', 'string', $parm);
                $quality = self::arguer('quality', 60, 'quality', $parm);
                switch ($type) {
                    case 'gif':
                        imagegif($object, $src_img);
                        break;
                    case 'jpg':
                        imagejpeg($object, $src_img, max(0, min(90, $quality)));
                        break;
                    case 'png':
                        imagepng($object, $src_img, min(9, max(1, 10 - floor($quality / 10))));
                        break;
                    case 'bmp':
                        self::imagebmp($object, $src_img);
                        break;
                    default:
                        return DEMON_CODE_MEDIA;
                }

                return $object;
                break;
            //  读取文件
            case 'read':
            default:
                $object = null;
                $type = null;
                $width = 0;
                $height = 0;
                //  读取文件
                if (!empty($src_img) && file_exists($src_img)) {
                    $info = getimagesize($src_img);
                    $type = self::_imageType()[$info[2]] ?? null;
                    $width = $info[0];
                    $height = $info[1];
                    //  取得水印图片的格式
                    switch ($type) {
                        case 'gif':
                            $object = imagecreatefromgif($src_img);
                            break;
                        case 'jpg':
                            $object = imagecreatefromjpeg($src_img);
                            break;
                        case 'png':
                            $object = imagecreatetruecolor($width, $height);
                            imagefill($object, 0, 0, imagecolorallocatealpha($object, 0, 0, 0, 127));
                            $background = imagecreatefrompng($src_img);
                            imagecopyresampled($object, $background, 0, 0, 0, 0, $width, $height, $width, $height);
                            imagesavealpha($object, true);
                            break;
                        case 'bmp':
                            $object = self::imagecreatefrombmp($src_img);
                            break;
                    }
                }

                return ['object' => $object, 'width' => $width, 'height' => $height, 'type' => $type];
                break;
        }
    }

    /**
     * 获取图片色调
     *
     * @param     $src_img
     *
     * @return Convert|mixed
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function imageColor($src_img)
    {
        //  获取文件信息
        $info = self::imageObject('read', $src_img);
        //  如果文件存在
        if ($info['object']) {
            $object = $info['object'];
            $width = 100;  //设置宽度
            $height = 100;  //设置高度
            $copy = imagecreatetruecolor($width, $height);
            imagecopyresampled($copy, $object, 0, 0, 0, 0, $width, $height, $info['width'], $info['height']);
            $rColorNum = $gColorNum = $bColorNum = $total = 0;
            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $rgb = imagecolorat($copy, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;
                    $rColorNum += $r;
                    $gColorNum += $g;
                    $bColorNum += $b;
                    $total++;
                }
            }

            //  返回计算结果
            return ['r' => round($rColorNum / $total), 'g' => round($gColorNum / $total), 'b' => round($bColorNum / $total)];
        }

        //  返回错误结果
        return ['r' => 0, 'g' => 0, 'b' => 0];
    }

    /**
     * 生成缩略图
     *
     * @param     $src_img //变更文件
     * @param     $parm    //参数
     *
     * @return int|array
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function imageThumb($src_img, $parm = [])
    {
        if (!is_file($src_img))
            return DEMON_CODE_NONE;
        //  缩略参数
        $width = $parm['width'] ?? 100;
        $height = $parm['height'] ?? 100;
        $mode = $parm['mode'] ?? 5;
        $background = $parm['background'] ?? '';
        $jpgForce = isset($parm['jpgForce']) ? $parm['jpgForce'] : true;
        $jpgName = $jpgForce ? rtrim($src_img, self::suffix($src_img)) . 'jpg' : $src_img;
        //  创建原始对象
        $imageInfo = self::imageObject('read', $src_img);
        $imageType = $imageInfo['type'];
        switch ($mode) {
            //  强制变成固定大小
            case 'force':
                $canvas = imagecreatetruecolor($width, $height);
                if (in_array($imageType, ['gif', 'png'])) {
                    imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
                    imagecopyresampled($canvas, $imageInfo['object'], 0, 0, 0, 0, $width, $height, $imageInfo['width'], $imageInfo['height']);
                    imagesavealpha($canvas, true);
                }
                else imagecopyresampled($canvas, $imageInfo['object'], 0, 0, 0, 0, $width, $height, $imageInfo['width'], $imageInfo['height']);
                $image = $canvas;
                break;
            //  按照比例在大小内进行缩放，最小边为设置的边，另一边等比例变化
            case 'scale':
                $src_width = $imageInfo['width'];
                $src_height = $imageInfo['height'];
                $dst_width = $width;
                $dst_height = $height;
                if ($src_width * $height > $src_height * $width)
                    $dst_height = intval($width * $src_height / $src_width);
                else $dst_width = intval($height * $src_width / $src_height);
                $canvas = imagecreatetruecolor($dst_width, $dst_height);
                if (in_array($imageType, ['gif', 'png'])) {
                    imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
                    imagecopyresampled($canvas, $imageInfo['object'], 0, 0, 0, 0, $dst_width, $dst_height, $src_width, $src_height);
                    imagesavealpha($canvas, true);
                }
                else imagecopyresampled($canvas, $imageInfo['object'], 0, 0, 0, 0, $dst_width, $dst_height, $src_width, $src_height);
                $image = $canvas;
                break;
            //  按照比例在大小内缩放，并且填充底色
            case 'scale_fill':
                $src_width = $imageInfo['width'];
                $src_height = $imageInfo['height'];
                $x = 0;
                $y = 0;
                $dst_width = $width;
                $dst_height = $height;
                if ($src_width * $height > $src_height * $width) {
                    $dst_height = intval($width * $src_height / $src_width);
                    $y = intval(($height - $dst_height) / 2);
                }
                else {
                    $dst_width = intval($height * $src_width / $src_height);
                    $x = intval(($width - $dst_width) / 2);
                }
                //  获取主色调
                if (!$background)
                    $background = self::imageColor($src_img);
                //  缩放类型
                $canvas = imagecreatetruecolor($width, $height);
                if (in_array($imageType, ['gif', 'png'])) {
                    imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
                    imagecopyresampled($canvas, $imageInfo['object'], $x, $y, 0, 0, $dst_width, $dst_height, $src_width, $src_height);
                    imagesavealpha($canvas, true);
                }
                else {
                    imagefill($canvas, 0, 0, imagecolorallocate($canvas, $background['r'], $background['g'], $background['b']));
                    imagecopyresampled($canvas, $imageInfo['object'], $x, $y, 0, 0, $dst_width, $dst_height, $src_width, $src_height);
                }
                $image = $canvas;
                break;
            //  智能模式，获取指定范围内的内容（1为顶端居左，2为顶端居中，3为顶端居右；4为中部居左，5为中部居中，6为中部居右；7为底端居左，8为底端居中，9为底端居右）
            default:
                $src_width = $imageInfo['width'];
                $src_height = $imageInfo['height'];
                $dst_width = $src_width;
                $dst_height = $src_height;
                if ($src_width * $height > $src_height * $width)
                    $dst_width = intval($src_height * $width / $height);
                else $dst_height = intval($src_width * $height / $width);
                switch ($mode) {
                    case 1:
                        $x = 0;
                        $y = 0;
                        break;
                    case 2:
                        $x = intval(($src_width - $dst_width) / 2);
                        $y = 0;
                        break;
                    case 3:
                        $x = $src_width - $dst_width;
                        $y = 0;
                        break;
                    case 4:
                        $x = 0;
                        $y = intval(($src_height - $dst_height) / 2);
                        break;
                    case 5:
                        $x = intval(($src_width - $dst_width) / 2);
                        $y = intval(($src_height - $dst_height) / 2);
                        break;
                    case 6:
                        $x = $src_width - $dst_width;
                        $y = intval(($src_height - $dst_height) / 2);
                        break;
                    case 7:
                        $x = 0;
                        $y = $src_height - $dst_height;
                        break;
                    case 8:
                        $x = intval(($src_width - $dst_width) / 2);
                        $y = $src_height - $dst_height;
                        break;
                    case 9:
                        $x = $src_width - $dst_width;
                        $y = $src_height - $dst_height;
                        break;
                    default:
                        $x = intval(($src_width - $dst_width) / 2);
                        $y = intval(($src_height - $dst_height) / 2);
                }
                //  缩放类型
                $canvas = imagecreatetruecolor($width, $height);
                if (in_array($imageType, ['gif', 'png'])) {
                    imagecopyresampled($canvas, $imageInfo['object'], 0, 0, $x, $y, $width, $height, $dst_width, $dst_height);
                    imagesavealpha($canvas, true);
                }
                else imagecopyresampled($canvas, $imageInfo['object'], 0, 0, $x, $y, $width, $height, $dst_width, $dst_height);
                $image = $canvas;
                break;
        }

        //  文件保存
        if ($jpgForce) {
            imagejpeg($image, $jpgName, 75);
            if ($jpgName != $src_img)
                unlink($src_img);
        }
        else {
            $foo = self::imageObject('write', $src_img, ['object' => $image, 'quality' => 75, 'type' => $imageType]);
            if (is_numeric($foo))
                return $foo;
        }

        //  返回缩略图地址
        return ['img' => $src_img];
    }

    /**
     * 图片水印 (水印支持图片或文字)
     *
     * @param       $groundImage
     * @param array $parm
     *
     * @return array|int
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function imageMark($groundImage, $parm = [])
    {
        //  水印位置，有10种状态，0为随机位置；1为顶端居左，2为顶端居中，3为顶端居右；4为中部居左，5为中部居中，6为中部居右；7为底端居左，8为底端居中，9为底端居右；
        $waterPos = $parm['waterPos'] ?? 9;
        //  图片水印，即作为水印的图片，暂只支持GIF,JPG,PNG格式；
        $waterImage = $parm['waterImage'] ?? '';
        //  背景图片，主要用于透明图片的背景纹理平铺
        $textureImage = $parm['textureImage'] ?? '';
        //  文字水印，即把文字作为为水印，支持ASCII码，不支持中文；
        $waterText = $parm['waterText'] ?? ' ';
        $waterFont = $parm['waterFont'] ?? '';
        //  文字大小，像素尺寸；
        $waterSize = $parm['waterSize'] ?? 28;
        //  文字颜色，值为十六进制颜色值，默认为#FF0000(红色)；
        $waterColor = $parm['waterColor'] ?? '#FFFFA4';
        $waterStroke = $parm['waterStroke'] ?? '#36130A';
        //  最终文件质量
        $quality = $parm['quality'] ?? 60;
        $isWaterImage = $parm['isWaterImage'] ?? false;
        $waterW = $parm['waterW'] ?? false;
        $waterH = $parm['waterH'] ?? false;
        $jpgForce = $parm['jpgForce'] ?? true;
        $jpgName = $jpgForce ? rtrim($groundImage, self::suffix($groundImage)) . 'jpeg' : $groundImage;
        $gifSkip = $parm['gifSkip'] ?? false;
        $skip = false;
        //  申明图片对象
        $waterObj = $textureObj = $groundObj = $imgType = $newObj = null;
        //  读取水印文件
        if (!empty($waterImage) && file_exists($waterImage)) {
            $isWaterImage = true;
            $waterInfo = self::imageObject('read', $waterImage);
            $waterW = $waterInfo['width'];  //取得水印图片的宽
            $waterH = $waterInfo['height'];  //取得水印图片的高
            //  取得水印图片的格式
            switch ($waterInfo['type']) {
                case 'gif':
                    $isWaterImage = false;
                    break;
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'bmp':
                case 'webp':
                    $waterObj = $waterInfo['object'];
                    break;
                default:
                    return DEMON_CODE_MEDIA;
            }
        }

        //  读取背景图片
        if (!empty($groundImage) && file_exists($groundImage)) {
            $info = getimagesize($groundImage);
            $imageType = self::_imageType();
            $imgType = $imageType[$info[2]] ?? null;
            $groundW = $info[0];    //取得背景图片的宽
            $groundH = $info[1];    //取得背景图片的高
            //  取得背景图片的格式
            switch ($imgType) {
                case 'gif':
                    if ($imgType == 'gif' && $gifSkip)
                        return ['img' => $groundImage];
                    $fooInfo = self::imageObject('read', $groundImage);
                    $fooObj = $fooInfo['object'];
                    $groundObj = imagecreatetruecolor($groundW, $groundH);
                    //  平铺纹理
                    if ($textureImage && $jpgForce) {
                        $textureInfo = self::imageObject('read', $textureImage);
                        $textureObj = $textureInfo['object'];
                        imagesettile($groundObj, $textureObj);
                        imagefilledrectangle($groundObj, 0, 0, $groundW, $groundH, IMG_COLOR_TILED);
                        imagecopy($groundObj, $fooObj, 0, 0, 0, 0, $groundW, $groundH);
                    }
                    else $groundObj = $fooObj;
                    break;
                case 'jpg':
                case 'jpeg':
                    $groundObj = imagecreatefromjpeg($groundImage);
                    break;
                case 'png':
                case 'webp':
                    $fooInfo = self::imageObject('read', $groundImage);
                    $fooObj = $fooInfo['object'];
                    $groundObj = imagecreatetruecolor($groundW, $groundH);
                    //  平铺纹理
                    if ($textureImage && $jpgForce) {
                        $textureInfo = self::imageObject('read', $textureImage);
                        $textureObj = $textureInfo['object'];
                        imagesettile($groundObj, $textureObj);
                        imagefilledrectangle($groundObj, 0, 0, $groundW, $groundH, IMG_COLOR_TILED);
                        imagecopy($groundObj, $fooObj, 0, 0, 0, 0, $groundW, $groundH);
                    }
                    else $groundObj = $fooObj;
                    break;
                case 'bmp':
                    $groundObj = self::imagecreatefrombmp($groundImage);
                    break;
                default:
                    return DEMON_CODE_MEDIA;
            }
        }
        else return DEMON_CODE_MEDIA;

        //  图片水印
        if ($isWaterImage) {
            $w = $waterW;
            $h = $waterH;
        }
        //  文字水印
        else {
            //  取得使用 TrueType 字体的文本的范围
            $foo = imagettfbbox($waterSize, 0, $waterFont, $waterText);
            $w = ($foo[4] - $foo[6]) * 0.8;
            $h = ($foo[3] - $foo[5]) * 1.1;
            unset($foo);
        }

        //  如果宽高不达标则标记跳过（不打水印，其他照常处理）
        if (($groundW < $w * 2.56) || ($groundH < $h * 2.56))
            $skip = true;

        //  水印位置
        switch ($waterPos) {
            //  随机
            case 0:
                $posX = rand(0, ($groundW - $w));
                $posY = rand(0, ($groundH - $h));
                break;
            //  1为顶端居左
            case 1:
                $posX = 0;
                $posY = 0;
                break;
            //  2为顶端居中
            case 2:
                $posX = ($groundW - $w) / 2;
                $posY = 0;
                break;
            //  3为顶端居右
            case 3:
                $posX = $groundW - $w;
                $posY = 0;
                break;
            //  4为中部居左
            case 4:
                $posX = 0;
                $posY = ($groundH - $h) / 2;
                break;
            //  5为中部居中
            case 5:
                $posX = ($groundW - $w) / 2;
                $posY = ($groundH - $h) / 2;
                break;
            //  6为中部居右
            case 6:
                $posX = $groundW - $w;
                $posY = ($groundH - $h) / 2;
                break;
            //  7为底端居左
            case 7:
                $posX = 0;
                $posY = $groundH - $h;
                break;
            //  8为底端居中
            case 8:
                $posX = ($groundW - $w) / 2;
                $posY = $groundH - $h;
                break;
            //  9为底端居右
            case 9:
                $posX = $groundW - $w;
                $posY = $groundH - $h;
                break;
            //  随机
            default:
                $posX = rand(0, ($groundW - $w));
                $posY = rand(0, ($groundH - $h));
                break;
        }

        if (!$skip) {
            if (!$isWaterImage) {
                $R = hexdec(substr($waterStroke, 1, 2));
                $G = hexdec(substr($waterStroke, 3, 2));
                $B = hexdec(substr($waterStroke, 5));
                imagettftext($groundObj, $waterSize / 96 * 72, 0, $posX + 1, $posY + $waterSize + 1, imagecolorallocate($groundObj, $R, $G, $B), $waterFont, $waterText);
                $R = hexdec(substr($waterColor, 1, 2));
                $G = hexdec(substr($waterColor, 3, 2));
                $B = hexdec(substr($waterColor, 5));
                imagettftext($groundObj, $waterSize / 96 * 72, 0, $posX, $posY + $waterSize, imagecolorallocate($groundObj, $R, $G, $B), $waterFont, $waterText);
            }
            else {
                //  合并图片
                switch ($imgType) {
                    case 'gif':
                    case 'jpg':
                    case 'png':
                    case 'bmp':
                        imagecopy($groundObj, $waterObj, $posX, $posY, 0, 0, $waterW, $waterH);
                        break;
                    default:
                        return DEMON_CODE_MEDIA;
                }
            }
        }

        //  开始保存
        if (!$jpgForce) {
            $foo = self::imageObject('write', $groundImage, ['object' => $groundObj, 'quality' => $quality, 'type' => $imgType]);
            if (is_numeric($foo))
                return $foo;
        }
        //  保存为JPG格式图片
        if ($jpgForce) {
            imagejpeg($groundObj, $jpgName, max(0, min(90, $quality)));
            if ($jpgName != $groundImage)
                unlink($groundImage);
        }

        //  返回水印图片地址
        return self::arrayToObject(['img' => $jpgName, 'type' => $jpgForce ? 'jpeg' : $imgType]);
    }

    /**
     * 图片合并
     *
     * @param       $imgList
     * @param array $parm
     *
     * @return mixed
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function imageMerge($imgList, $parm = [])
    {
        //  画布宽高
        $bg_w = $parm['width'] ?? 720;
        $bg_h = $parm['height'] ?? 1280;
        //  创建背景图片
        $background = imagecreatetruecolor($bg_w, $bg_h);
        //  生成图片类型
        $img = $parm['img'] ?? 'png';
        //  图片质量
        $quality = $parm['quality'] ?? 60;
        //  填充透明背景色
        imagefill($background, 0, 0, imagecolorallocatealpha($background, 0, 0, 0, 127));
        //  循环叠加
        foreach ($imgList as $key => $val) {
            //  自定义图片属性
            if (is_array($val)) {
                $start_x = $val['x'] ?? 0;
                $start_y = $val['y'] ?? 0;
            }
            else {
                $start_x = 0;
                $start_y = 0;
            }
            $foo = self::imageObject('read', $val['src']);
            imagecopyresampled($background, $foo['object'], 0, 0, $start_x, $start_y, $bg_w, $bg_h, $bg_w, $bg_h);
            imagesavealpha($background, true);
        }
        //  保存类型
        $type = $parm['type'] ?? 'base64';
        //  开始保存到物理文件
        if (isset($parm['path'])) {
            self::fileCreate($parm['path'], '');
            self::imageObject('write', $parm['path'], ['object' => $background, 'quality' => $quality, 'type' => $img]);
        }
        //  返回类型
        switch ($type) {
            //  返回BASE64图片码
            case 'base64':
                return 'data:image/' . $img . ';base64,' . base64_encode($parm['path']);
                break;
            //  直接查看
            case 'view':
                header('Content-type: image/' . $img);
                echo file_get_contents($parm['path']);

                return true;
                break;
        }

        return true;
    }

    /**
     * 读取BMP文件
     *
     * @param $filename
     *
     * @return bool|resource
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function imagecreatefrombmp($filename)
    {
        if (!$f1 = fopen($filename, 'rb'))
            return false;
        $FILE = @unpack('vfile_type/Vfile_size/Vreserved/Vbitmap_offset', fread($f1, 14));
        if ($FILE['file_type'] != 19778)
            return false;
        $BMP = @unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel' . '/Vcompression/Vsize_bitmap/Vhoriz_resolution' . '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1, 40));
        $BMP['colors'] = pow(2, $BMP['bits_per_pixel']);
        if ($BMP['size_bitmap'] == 0)
            $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
        $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel'] / 8;
        $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
        $BMP['decal'] = ($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
        $BMP['decal'] -= floor($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
        $BMP['decal'] = 4 - (4 * $BMP['decal']);
        if ($BMP['decal'] == 4)
            $BMP['decal'] = 0;
        $PALETTE = [];
        if ($BMP['colors'] < 16777216)
            $PALETTE = @unpack('V' . $BMP['colors'], fread($f1, $BMP['colors'] * 4));
        $IMG = fread($f1, $BMP['size_bitmap']);
        $VIDE = chr(0);
        $res = imagecreatetruecolor($BMP['width'], $BMP['height']);
        $P = 0;
        $Y = $BMP['height'] - 1;
        while ($Y >= 0) {
            $X = 0;
            while ($X < $BMP['width']) {
                if ($BMP['bits_per_pixel'] == 32) {
                    $COLOR = @unpack('V', substr($IMG, $P, 3));
                    $B = ord(substr($IMG, $P, 1));
                    $G = ord(substr($IMG, $P + 1, 1));
                    $R = ord(substr($IMG, $P + 2, 1));
                    $color = imagecolorexact($res, $R, $G, $B);
                    if ($color == -1)
                        $color = imagecolorallocate($res, $R, $G, $B);
                    $COLOR[0] = $R * 256 * 256 + $G * 256 + $B;
                    $COLOR[1] = $color;
                }
                else if ($BMP['bits_per_pixel'] == 24)
                    $COLOR = @unpack('V', substr($IMG, $P, 3) . $VIDE);
                else if ($BMP['bits_per_pixel'] == 16) {
                    $COLOR = @unpack('n', substr($IMG, $P, 2));
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                }
                else if ($BMP['bits_per_pixel'] == 8) {
                    $COLOR = @unpack('n', $VIDE . substr($IMG, $P, 1));
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                }
                else if ($BMP['bits_per_pixel'] == 4) {
                    $COLOR = @unpack('n', $VIDE . substr($IMG, floor($P), 1));
                    if (($P * 2) % 2 == 0)
                        $COLOR[1] = ($COLOR[1] >> 4);
                    else
                        $COLOR[1] = ($COLOR[1] & 0x0F);
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                }
                else if ($BMP['bits_per_pixel'] == 1) {
                    $COLOR = @unpack('n', $VIDE . substr($IMG, floor($P), 1));
                    if (($P * 8) % 8 == 0)
                        $COLOR[1] = $COLOR[1] >> 7;
                    else if (($P * 8) % 8 == 1)
                        $COLOR[1] = ($COLOR[1] & 0x40) >> 6;
                    else if (($P * 8) % 8 == 2)
                        $COLOR[1] = ($COLOR[1] & 0x20) >> 5;
                    else if (($P * 8) % 8 == 3)
                        $COLOR[1] = ($COLOR[1] & 0x10) >> 4;
                    else if (($P * 8) % 8 == 4)
                        $COLOR[1] = ($COLOR[1] & 0x8) >> 3;
                    else if (($P * 8) % 8 == 5)
                        $COLOR[1] = ($COLOR[1] & 0x4) >> 2;
                    else if (($P * 8) % 8 == 6)
                        $COLOR[1] = ($COLOR[1] & 0x2) >> 1;
                    else if (($P * 8) % 8 == 7)
                        $COLOR[1] = ($COLOR[1] & 0x1);
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                }
                else
                    return false;
                imagesetpixel($res, $X, $Y, $COLOR[1]);
                $X++;
                $P += $BMP['bytes_per_pixel'];
            }
            $Y--;
            $P += $BMP['decal'];
        }
        fclose($f1);

        return $res;
    }

    /**
     * 创建bmp格式图片
     *
     * @param resource $im          图像资源
     * @param string   $filename    如果要另存为文件，请指定文件名，为空则直接在浏览器输出
     * @param int      $bit         图像质量(1、4、8、16、24、32位)
     * @param int      $compression 压缩方式，0为不压缩，1使用RLE8压缩算法进行压缩
     *
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function imagebmp(&$im, $filename = '', $bit = 8, $compression = 0)
    {
        if (!in_array($bit, [1, 4, 8, 16, 24, 32]))
            $bit = 8;
        else if ($bit == 32)
            $bit = 24;
        $bits = pow(2, $bit);
        // 调整调色板
        imagetruecolortopalette($im, true, $bits);
        $width = imagesx($im);
        $height = imagesy($im);
        $colors_num = imagecolorstotal($im);
        // 颜色索引
        $rgb_quad = '';
        if ($bit <= 8) {
            for ($i = 0; $i < $colors_num; $i++) {
                $colors = imagecolorsforindex($im, $i);
                $rgb_quad .= chr($colors['blue']) . chr($colors['green']) . chr($colors['red']) . "\0";
            }
            // 位图数据
            $bmp_data = '';
            // 非压缩
            if ($compression == 0 || $bit < 8) {
                if (!in_array($bit, [1, 4, 8]))
                    $bit = 8;
                $compression = 0;
                // 每行字节数必须为4的倍数，补齐。
                $extra = '';
                $padding = 4 - ceil($width / (8 / $bit)) % 4;
                if ($padding % 4 != 0)
                    $extra = str_repeat("\0", $padding);
                for ($j = $height - 1; $j >= 0; $j--) {
                    $i = 0;
                    while ($i < $width) {
                        $bin = 0;
                        $limit = $width - $i < 8 / $bit ? (8 / $bit - $width + $i) * $bit : 0;
                        for ($k = 8 - $bit; $k >= $limit; $k -= $bit) {
                            $index = imagecolorat($im, $i, $j);
                            $bin |= $index << $k;
                            $i++;
                        }
                        $bmp_data .= chr($bin);
                    }
                    $bmp_data .= $extra;
                }
            }
            // RLE8 压缩
            else if ($compression == 1 && $bit == 8) {
                for ($j = $height - 1; $j >= 0; $j--) {
                    $last_index = "\0";
                    $same_num = 0;
                    for ($i = 0; $i <= $width; $i++) {
                        $index = imagecolorat($im, $i, $j);
                        if ($index !== $last_index || $same_num > 255) {
                            if ($same_num != 0)
                                $bmp_data .= chr($same_num) . chr($last_index);
                            $last_index = $index;
                            $same_num = 1;
                        }
                        else $same_num++;
                    }
                    $bmp_data .= "\0\0";
                }
                $bmp_data .= "\0\1";
            }
            $size_quad = strlen($rgb_quad);
            $size_data = strlen($bmp_data);
        }
        else {
            // 每行字节数必须为4的倍数，补齐。
            $extra = '';
            $padding = 4 - ($width * ($bit / 8)) % 4;
            if ($padding % 4 != 0)
                $extra = str_repeat("\0", $padding);
            // 位图数据
            $bmp_data = '';
            for ($j = $height - 1; $j >= 0; $j--) {
                for ($i = 0; $i < $width; $i++) {
                    $index = imagecolorat($im, $i, $j);
                    $colors = imagecolorsforindex($im, $index);
                    if ($bit == 16) {
                        $bin = 0 << $bit;
                        $bin |= ($colors['red'] >> 3) << 10;
                        $bin |= ($colors['green'] >> 3) << 5;
                        $bin |= $colors['blue'] >> 3;
                        $bmp_data .= pack('v', $bin);
                    }
                    else $bmp_data .= pack('c*', $colors['blue'], $colors['green'], $colors['red']);
                }
                $bmp_data .= $extra;
            }
            $size_quad = 0;
            $size_data = strlen($bmp_data);
            $colors_num = 0;
        }
        // 位图文件头
        $file_header = 'BM' . pack('V3', 54 + $size_quad + $size_data, 0, 54 + $size_quad);
        // 位图信息头
        $info_header = pack('V3v2V*', 0x28, $width, $height, 1, $bit, $compression, $size_data, 0, 0, $colors_num, 0);
        // 写入文件
        if ($filename != '') {
            $fp = fopen($filename, 'wb');
            fwrite($fp, $file_header);
            fwrite($fp, $info_header);
            fwrite($fp, $rgb_quad);
            fwrite($fp, $bmp_data);
            fclose($fp);
        }
    }
    /**
     * 判断是否是函数
     *
     * @param      $var
     * @param bool $name
     *
     * @return bool
     *
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function isFunction($var, $name = true)
    {
        return ($name && is_string($var) && function_exists($var)) || (is_object($var) && ($var instanceof Closure));
    }

    /**
     * 写文本日志
     *
     * @param string $path 文件路径
     * @param string $file 文件名（如）
     * @param string $message
     * @param array  $data
     * @param bool   $daily
     *
     * @return false|int
     *
     * @copyright 魔网天创信息科技
     * @author    ComingDemon
     */
    public function writeLog(string $path, string $file = '', string $message = '', $data = [], $daily = true)
    {
        !is_dir($path) && mkdir($path, 0755, true);
        $file = $daily ? (($file ? ($file . '.') : '') . date('Ymd') . '.log') : ($file ? : 'log') . '.log';

        return file_put_contents($path . DIRECTORY_SEPARATOR . $file, '[' . date('Y-m-d H:i:s') . '] ' . $message . ' ' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND);
    }
}