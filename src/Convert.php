<?php
namespace Demon\Library;


class Convert
{
    /**
     * @var object 对象实例
     */
    protected static $instance;

    /**
     * 初始化
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @return Convert
     */
    static public function instance()
    {
        if (is_null(self::$instance))
            self::$instance = new static();

        return self::$instance;
    }

    /**
     * 对象合并
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     *
     * @return mixed
     */
    public function object_merge()
    {
        //  获取参数
        $parm = func_get_args();
        $tempArray = [];
        foreach ($parm as $key => $val)
            $tempArray = array_merge($tempArray, (array)$val);

        return (object)$tempArray;
    }

    /**
     * 对象克隆
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param $object
     *
     * @return mixed
     */
    public function object_clone($object)
    {
        return $this->array_to_object($this->object_to_array($object));
    }

    /**
     * 对象排序
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param $object
     * @param $type
     */
    public function object_sort(&$object, $type)
    {
        if ($object) {
            $object = $this->object_to_array($object);
            $type($object);
            $object = (object)$this->array_to_object($object);
            //  新对象
            $newObject = new \stdClass();
            foreach ($object as $key => $val) {
                //  保证对象是文本键名
                $newObject->{(string)$key} = $val;
            }
            $object = $newObject;
        }
    }

    /**
     * 对象过滤器
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param array $object //  对象或数组
     * @param array $filter //  过滤器数组
     * @param int   $mod    //  小于0表示黑名单模式，大于0表示白名单模式
     *
     * @return array
     */
    public function object_filter(&$object = [], $filter = [], $mod = -1)
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
                    if ($dataType == 'object')
                        unset($object->{$key});
                    continue;
                }
                //  如果是白名单模式并且不在白名单则销毁
                else if ($mod > 0 && !in_array($key, $filter)) {
                    if ($dataType == 'array')
                        unset($object[$key]);
                    if ($dataType == 'object')
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
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param $object
     *
     * @return int
     */
    public function object_count($object)
    {
        return count($this->object_to_array($object));
    }

    /**
     * 将对象转换为数组
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param $object //对象
     *
     * @return mixed
     */
    public function object_to_array($object)
    {
        $object = json_decode(json_encode($object), true);

        return $object;
    }

    /**
     * 将数组转换为对象
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param $array //数组
     *
     * @return mixed
     */
    public function array_to_object($array)
    {
        $array = json_decode(json_encode($array));

        return $array;
    }

    /**
     * XML转换为对象
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param $xml
     *
     * @return mixed
     */
    public function xml_to_object($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);

        //  返回结果
        $object = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)));

        //  进入递归（将子节点的item标签设置为主节点内容）
        $this->_xml_to_object($object);

        //  返回结果
        return $object;
    }

    private function _xml_to_object(&$data)
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
                $this->_xml_to_object($val);
            }
        }
    }

    /**
     * 对象转换为XML
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param        $object
     * @param null   $doccment
     * @param null   $item
     * @param string $root
     * @param bool   $isFormat
     *
     * @return string
     */
    public function object_to_xml($object, $doccment = null, $item = null, $root = 'xml', $isFormat = true)
    {
        //  首次申明对象
        if (!$doccment) {
            $doccment = new \DOMDocument("1.0");
            $doccment->encoding = 'UTF-8';
            $object = $this->object_to_array($object);
        }
        //  首次申明结构体
        if (!$item) {
            $item = $doccment->createElement($root);
            $doccment->appendChild($item);
        }
        //  循环插入节点
        foreach ($object as $key => $val) {
            $itemx = $doccment->createElement(is_string($key) ? $key : "item");
            $item->appendChild($itemx);
            if (!is_array($val)) {
                $text = $doccment->createTextNode($val);
                $itemx->appendChild($text);
            }
            else
                $this->object_to_xml($val, $doccment, $itemx);
        }

        //  生成结果
        $data = $doccment->saveXML();
        //  如果非格式化，则去掉标签头
        if (!$isFormat)
            $data = str_replace(['<?xml version="1.0" encoding="UTF-8"?>', "\n"], '', $data);

        //  返回结果
        return $data;
    }

    /**
     * 计算数字长度
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param $num
     *
     * @return int
     */
    public function num_count($num)
    {
        return strlen((int)($num));
    }

    /**
     * 检查字符串是否出现在数组中
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param            $string
     * @param            $arrry
     * @param bool|false $returnvalue
     *
     * @return bool
     */
    public function array_strpos($string, $arrry, $returnvalue = false)
    {
        if (empty($string))
            return false;

        foreach ((array)$arrry as $val) {
            if (stripos($string, $val) !== false) {
                $return = $returnvalue ? $val : true;

                return $return;
            }
        }

        return false;
    }

    /**
     *   修改数字下标为内容中的键值
     * @author      ComingDemon
     * @copyright   新链云科技
     *
     * @param        $array
     * @param string $index
     *
     * @return array
     */
    public function array_index($array, $index = '')
    {
        $isObj = false;
        if ($index) {
            if (is_object($array)) {
                $isObj = true;
                $array = $this->object_to_array($array);
            }
            $array = array_values($array);
            $newArray = [];
            foreach ($array as $val) {
                if (is_object($array[0]))
                    $newArray[$val->$index] = $this->array_to_object($val);
                else
                    $newArray[$val[$index]] = $val;
            }

            return $isObj ? $this->array_to_object($newArray) : $newArray;
        }

        return $array;
    }

    /**
     * 获取数组维度
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param $array
     *
     * @return mixed
     */
    public function array_level($array)
    {
        $arraylevel = [0];

        $this->_array_level($array, $arraylevel, 0);

        return max($arraylevel);
    }

    private function _array_level($array, &$arraylevel, $level = 0)
    {
        if (is_array($array)) {
            $level++;
            $arraylevel[] = $level;
            foreach ($array as $val) {
                $this->_array_level($val, $arraylevel, $level);
            }
        }
    }

    /**
     * 随机取出一个值
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param array $array
     *
     * @return mixed|null
     */
    public function array_rand($array = [])
    {
        if (!$array)
            return null;

        return $array[rand(0, count($array) - 1)];
    }

    /**
     * 数组排序
     * @author    ComingDemon
     * @copyright 新链云科技
     * @return bool|mixed|null
     */
    public function array_reorder()
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
                foreach ($arr as $index => $val) {
                    $foo[$index] = $val[$field];
                }
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
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param array $array  //原数组
     * @param array $array2 //需要去掉的值
     * @param bool  $reset  //是否重排
     *
     * @return array
     */
    public function array_unset(&$array = [], $array2 = [], $reset = true)
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

        //  是否重新排列数组键
        if ($reset)
            $array = array_merge($array);

        //  返回处理后数组
        return $array;
    }

    /**
     * 创建一个随机字符串
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param int    $length //随机长度
     * @param string $type   //随机类型
     *
     * @return string
     */
    public function rand($length = 6, $type = 'all')
    {
        if (!$length)
            return '';
        switch ($type) {
            //  全部
            case 'all':
                $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
                break;
            //  数字
            case 'num':
                $pattern = '1234567890';
                break;
            //  字母
            case 'letter':
                $pattern = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
                break;
            //  自定义
            default:
                $pattern = $type;
        }
        $key = '';
        for ($i = 0; $i < $length; $i++) {
            $key .= $pattern[mt_rand(0, strlen($pattern) - 1)];
        }

        return (string)$key;
    }

    /**
     * 通过密码生成一个加密密码
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param $parm //  参数
     *
     * @return mixed
     */
    public function password($parm = [])
    {
        //  传入内容
        $content = $parm['content'] ?? '';
        //  是否预先MD5处理
        $md5 = $parm['md5'] ?? false;
        //  如果已经是32位的MD5则截取
        if ($md5 === 32 || mb_strlen($content) == 32)
            $content = strtolower(substr($content, 8, 16));
        //  如果不是MD5则加密为16位MD5
        else if (!$md5)
            $content = strtolower(substr(md5($content), 8, 16));

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
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param        $value //数值
     * @param int    $long  //精度位数
     * @param string $type  //截取类型
     *
     * @return string
     */
    public function double($value, $long = 2, $type = 'floor')
    {
        $pow = pow(10, $long);
        switch ($type) {
            case 'floor':
                return sprintf("%0." . $long . "f", floor($value * $pow) / $pow);
            case 'round':
                return $this->double(round($value, $long), $long);
            case 'ceil':
                return sprintf("%0." . $long . "f", ceil($value * $pow) / $pow);
            case 'integer':
                return $this->double(floor($value), $long);
            case 'decimals':
                return $long < 0 ? (string)($value - floor($value)) : $this->double($value - floor($value), $long);
        }

        //  返回处理结果
        return $value;
    }

    /**
     * 数字节点转字符串
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param            $number //原数字
     * @param int        $digit  //节点位数
     * @param string     $string //节点字符
     * @param bool|false $force  //是否强制(不判断符合位数)
     *
     * @return string
     */
    public function num_to_char($number, $digit = 4, $string = 'k', $force = false)
    {
        //  判断数字是否达到转换标准
        $minNumber = '1';
        for ($i = 1; $i < $digit; $i++) {
            $minNumber .= '0';
        }
        if ($number > $minNumber * 10 || $force) {
            $newNumber = floor($number / $minNumber * 10) / 10;

            return $newNumber . $string;
        }
        else
            return $number;
    }

    /**
     * 数字最大值返回
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param        $number
     * @param        $max
     * @param string $string
     *
     * @return string
     */
    public function num_to_max($number, $max, $string = '')
    {
        return min($number, $max) == $max ? ($string ? : $max . '+') : $number;
    }

    /**
     * 取摘要信息
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param $string
     * @param $length
     * @param $charset
     *
     * @return mixed|string
     */
    public function digest($string, $length, $charset = 'utf-8')
    {
        return mb_substr(str_replace(["\r", "\n", "\r\n", PHP_EOL, "&nbsp;"], "", trim(strip_tags((string)nl2br($string)))), 0, $length, $charset ? : 'utf-8');
    }

    /**
     * 生成一个订单码
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param int    $type    //订单码类型（000:充值）
     * @param int    $length  //订单码长度
     * @param bool   $isTime  //是否带时间前缀
     * @param string $pre     //额外前缀（最大不能超过4位数）
     * @param string $preType //前缀位置（center:中间,left:左侧）
     *
     * @return int|string
     */
    public function get_order($type = 0, $length = 20, $isTime = true, $pre = '', $preType = 'center')
    {
        //  前缀是时间
        if ($isTime) {
            $time = date('YmdHis');
            $length -= strlen($time);
            if ($length <= 3)
                return '';
            $pre = $time;
        }
        //  否则调用自定义前缀
        else {
            $pre = str_pad($pre, 4, "0", STR_PAD_LEFT);
            $length -= strlen($pre);
            if ($length <= 3)
                return '';
        }
        //  拼接最终码
        if (!$isTime) {
            if ($preType == 'center')
                $orderCode = str_pad($type, 3, "0", STR_PAD_LEFT) . $pre . $this->rand($length - 3, 'num');
            else
                $orderCode = $pre . $this->rand($length, 'num');
        }
        else
            $orderCode = $pre . str_pad($type, 3, "0", STR_PAD_LEFT) . $this->rand($length - 3, 'num');

        //  返回最终码
        return $orderCode;
    }

    /**
     * 字符串过长截取
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param $str
     * @param $length
     * @param $suffix
     *
     * @return string
     */
    public function cut_str($str, $length, $suffix = '...')
    {
        return mb_strimwidth($str, 0, $length, $suffix, 'utf-8');
    }

    /**
     * 名称隐藏截取
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param       $name
     * @param array $parm
     *
     * @return string
     */
    public function cut_name($name, $parm = [])
    {
        //  字符集
        $encoding = 'utf-8';
        //  前缀长度
        $firstLength = isset($parm['first']) ? $parm['first'] : 1;
        //  后缀长度
        $lastLength = isset($parm['last']) ? $parm['last'] : 1;
        //  获取当前字符长度
        $strlen = mb_strlen($name, $encoding);
        //  截取前面的字符
        $firstStr = mb_substr($name, 0, $firstLength, $encoding);
        $lastStr = mb_substr($name, -($lastLength), $lastLength, $encoding);
        //  如果总长度不足，则把尾部都变成*
        if ($strlen <= $firstLength + $lastLength)
            $string = $this->fill($firstLength + $lastLength, $firstStr, 'right', '*');
        else
            $string = $this->fill($strlen - mb_strlen($lastStr, $encoding), $firstStr, 'right', '*') . $lastStr;

        //  返回结果
        return $string;
    }

    /**
     * 不足0，位数补齐
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param $pre
     * @param $str
     * @param $type
     * @param $zero
     *
     * @return string
     */
    public function fill($pre, $str, $type = 'left', $zero = '0')
    {
        $nowLen = mb_strlen($str, 'UTF8');
        if ($nowLen < $pre) {
            $fill = '';
            for ($i = 0; $i < $pre - $nowLen; $i++)
                $fill = $zero . $fill;

            if ($type == 'left')
                return $fill . $str;
            else
                return $str . $fill;
        }

        return (string)$str;
    }

    /**
     * 毫秒时间戳转换日期格式
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param     $format
     * @param int $mstime
     *
     * @return false|mixed|string
     */
    public function msdate($format, $mstime = DEMON_MSTIME)
    {
        //  除以一千取出秒部分和毫秒部分
        $info = explode(".", (int)$mstime / 1000);
        //  将所有反斜杠的f变成其他字符串临时保存
        if (strpos($format, '\f') !== false) {
            $format = str_replace('\\f', '(.ω.)', $format);
            $cute = true;
        }
        //  将秒部分进行正常格式化
        $date = date($format, $info[0]);
        $info[1] = isset($info[1]) ? $info[1] : 0;
        //  如果包含小写f，则替换f为毫秒部分（自动补齐为3位数）
        if (strpos($date, 'f') !== false)
            $date = str_replace('f', $this->fill(3, $info[1], 'right'), $date);
        //  将临时字符串还原为f
        if ($cute??false)
            $date = str_replace('(.ω.)', 'f', $date);

        //  返回秒部分格式化结果
        return $date;
    }

    /**
     * 通过毫秒格式获取时间戳
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param     $msdate
     * @param int $time
     *
     * @return string
     */
    public function mstotime($msdate, $time = DEMON_TIME)
    {
        list($usec, $sec) = explode('.', $msdate);
        $date = strtotime($usec, $time);

        return str_pad($date . $sec, 13, '0', STR_PAD_RIGHT);
    }

    /**
     * 根据规则获取一个时间戳
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param        $rule   //内置strtotime规则（）
     * @param mixed  $format //如果要获取特殊的规则
     * @param mixed  $time   //起点时间戳
     * @param string $type   //mstime表示毫秒，默认为time秒
     *
     * @return false|int
     */
    public function control_time($rule, $format = 'Y-m-d H:i:s', $time = DEMON_MSTIME, $type = 'mstime')
    {
        $ratio = $type == 'mstime' ? 1000 : 1;

        return strtotime(date($format, strtotime($rule, $time / $ratio))) * $ratio;
    }

    /**
     * @todo      合并加密2个字符串并且MD5加密
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param        $string //目标字符串
     * @param string $salt   //盐值
     *
     * @return string
     */
    function sme($string, $salt = '')
    {
        if (!$string)
            return '';

        $newsalt = $salt ? md5($salt) : $salt;

        $n = 0;
        for ($i = 0; $i < 32; $i++) {
            $n += ord($newsalt[$i]);
        }

        $m = 0;
        for ($j = 0; $j < 6; $j++) {
            $m += ord(!empty($salt[$j]) ? $salt[$j] : '');
        }
        $salt = $n * (int)$m;
        $string = md5(md5($string) . $salt);

        return $string;
    }

    /**
     * @todo      字符串加密（异或加密，需要验证密钥和次数）
     * @author    ComingDemon
     * @copyright 新链云科技
     *
     * @param        $string //目标字符串
     * @param        $type   //类型（0：解密，1：加密）
     * @param string $key    //密钥
     * @param int    $num    //加密次数
     *
     * @return mixed|string
     */
    function sse($string, $type = 1, $key = '', $num = 2)
    {
        if (!$string)
            return '';

        if (!$key)
            $key = 'demon';

        //  将问号提前转义，解密的时候注意还原
        $string = str_replace('?', '<<\>>', $string);
        $key = $this->sme((string)$key, 'num' . $num);
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
        else {
            if (substr($result, 0, 8) == substr($key, 8, 8) && substr($result, -8, 8) == substr($key, 16, 8))
                return str_replace('<<\>>', '?', substr(substr($result, 8), 0, -8));
            else
                return '';
        }
    }
}