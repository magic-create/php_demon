<?php
/**
 * 本文件用于定义一些加密相关的内容
 * Created by M-Create.Team,
 * Copyright 魔网天创信息科技
 * User: ComingDemon
 * Date: 2019/3/14
 * Time: 10:21
 */

namespace Demon\Library;

class Rsa
{
    /**
     * @var object 对象实例
     */
    protected static $instance;

    public $private_key = '';
    public $public_key = '';
    public $private_pwd = '';
    public $pi_key;
    public $pu_key;

    /**
     * 初始化
     * @return $this
     * @copyright 魔网天创信息科技
     * @author    ComingDemon
     *
     */
    static function instance()
    {
        if (is_null(self::$instance))
            self::$instance = new static();

        return self::$instance;
    }

    /**
     * 设置私钥
     *
     * @param        $string
     *
     * @param string $passphrase
     *
     * @return $this
     * @copyright 魔网天创信息科技
     *
     * @author    ComingDemon
     */
    public function setPrivate($string, $passphrase = '')
    {
        $this->private_key = $string;
        $this->private_pwd = $passphrase;
        $this->pi_key = openssl_pkey_get_private($this->private_key, $this->private_pwd);

        return $this;
    }

    /**
     * 设置私钥
     *
     * @param $string
     *
     * @return $this
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function setPublic($string)
    {
        $this->public_key = $string;
        $this->pu_key = openssl_pkey_get_public($this->public_key);

        return $this;
    }

    /**
     * 私钥加密
     *
     * @param $data
     *
     * @return mixed
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function privateEncrypt($data)
    {
        $crypto = '';
        foreach (str_split($data, 117) as $chunk) {
            openssl_private_encrypt($chunk, $encryptData, $this->pi_key);
            $crypto .= $encryptData;
        }
        $encrypted = $this->base64Encode($crypto);

        return $encrypted;
    }

    /**
     * BASE64加码
     *
     * @param $string
     *
     * @return mixed|string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    function base64Encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(['+', '/', '='], ['-', '_', ''], $data);

        return $data;
    }

    /**
     * BASE64解码
     *
     * @param $string
     *
     * @return bool|string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    function base64Decode($string)
    {
        $data = str_replace(['-', '_'], ['+', '/'], $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }

        return base64_decode($data);
    }

    /**
     * 公钥解密
     *
     * @param $encrypted
     *
     * @return string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function publicDecrypt($encrypted)
    {
        $crypto = '';
        foreach (str_split($this->base64Decode($encrypted), 128) as $chunk) {
            openssl_public_decrypt($chunk, $decryptData, $this->pu_key);
            $crypto .= $decryptData;
        }

        return $crypto;
    }

    /**
     * 公钥加密
     *
     * @param $data
     *
     * @return mixed
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function publicEncrypt($data)
    {
        $crypto = '';
        foreach (str_split($data, 117) as $chunk) {
            openssl_public_encrypt($chunk, $encryptData, $this->pu_key);
            $crypto .= $encryptData;
        }
        $encrypted = $this->base64Encode($crypto);

        return $encrypted;
    }

    /**
     * 私钥解密
     *
     * @param $encrypted
     *
     * @return string
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     *
     */
    public function privateDecrypt($encrypted)
    {
        $crypto = '';
        foreach (str_split($this->base64Decode($encrypted), 128) as $chunk) {
            openssl_private_decrypt($chunk, $decryptData, $this->pi_key);
            $crypto .= $decryptData;
        }

        return $crypto;
    }
}
