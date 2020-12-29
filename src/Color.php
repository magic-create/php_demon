<?php
/**
 * 本文件用于定义一些颜色相关的内容
 * Created by M-Create.Team,
 * Copyright 魔网天创信息科技
 * User: ComingDemon
 * Date: 2020/12/29
 * Time: 18:50
 */

namespace Demon\Library;


class Color
{
    /**
     * @var int 红色[0-255]
     */
    public $red = 0;

    /**
     * @var int 绿色[0-255]
     */
    public $green = 0;

    /**
     * @var int 蓝色[0-255]
     */
    public $blue = 0;

    /**
     * @var float 色相[0-360]
     */
    public $hue = 0.0;

    /**
     * @var float 饱和度[0-1]
     */
    public $saturation = 0.0;

    /**
     * @var float 色调[0-1]
     */
    public $value = 0.0;

    /**
     * @var float 亮度[0-1]
     */
    public $lightness = 0.0;

    /**
     * @var string
     */
    public $hex = '000000';

    /**
     * @var object 对象实例
     */
    protected static $instance;

    /**
     * 初始化
     *
     * @return $this
     *
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
     * 设置RGB
     *
     * @param int $red
     * @param int $green
     * @param int $blue
     *
     * @return $this
     *
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function rgb($red = 0, $green = 0, $blue = 0)
    {
        //  数组快速处理
        if (is_array($red)) {
            $red = $red['red'] ?? 0;
            $green = $red['green'] ?? 0;
            $blue = $red['blue'] ?? 0;
        }
        //  设置内容
        $this->red = min(255, max(0, (int)$red));
        $this->green = min(255, max(0, (int)$green));
        $this->blue = min(255, max(0, (int)$blue));

        return $this;
    }

    /**
     * 设置HSV
     *
     * @param float $hue
     * @param float $saturation
     * @param float $value
     *
     * @return $this
     *
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function hsv($hue = 0.0, $saturation = 0.0, $value = 0.0)
    {
        //  数组快速处理
        if (is_array($hue)) {
            $hue = $hue['hue'] ?? 0;
            $saturation = $hue['saturation'] ?? 0;
            $value = $hue['value'] ?? 0;
        }
        //  设置内容
        $this->hue = min(360, max(0, (float)$hue));
        $this->saturation = min(1, max(0, (float)$saturation));
        $this->value = min(1, max(0, (float)$value));

        return $this;
    }

    /**
     * 设置HSL
     *
     * @param float $hue
     * @param float $saturation
     * @param float $lightness
     *
     * @return $this
     *
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function hsl($hue = 0.0, $saturation = 0.0, $lightness = 0.0)
    {
        //  数组快速处理
        if (is_array($hue)) {
            $hue = $hue['hue'] ?? 0;
            $saturation = $hue['saturation'] ?? 0;
            $lightness = $hue['lightness'] ?? 0;
        }
        //  设置内容
        $this->hue = min(360, max(0, (float)$hue));
        $this->saturation = min(1, max(0, (float)$saturation));
        $this->lightness = min(1, max(0, (float)$lightness));

        return $this;
    }

    /**
     * 设置HEX
     *
     * @param string $hex
     *
     * @return $this
     *
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function hex($hex = '000000')
    {
        //  设置内容
        $this->hex = str_replace('#', '', (string)$hex);

        return $this;
    }

    /**
     * RGB2HSV
     *
     * @return $this
     *
     * @copyright 魔网天创信息科技
     * @author    ComingDemon
     */
    public function rgb2hsv()
    {
        $rgb = [$this->red, $this->green, $this->blue];
        $max = max($rgb);
        $min = min($rgb);
        $diff = $max - $min;
        //  赋值
        if ($max == $min)
            $this->hue = 0;
        else if ($max == $this->red && $this->green >= $this->blue)
            $this->hue = 60 * (($this->green - $this->blue) / $diff);
        else if ($max == $this->red && $this->green < $this->blue)
            $this->hue = 60 * (($this->green - $this->blue) / $diff) + 360;
        else if ($max == $this->green)
            $this->hue = 60 * (($this->blue - $this->red) / $diff) + 120;
        else if ($max == $this->blue)
            $this->hue = 60 * (($this->red - $this->green) / $diff) + 240;
        $this->hue /= 360;
        $this->saturation = $max ? 1 - $min / $max : 0;
        $this->value = $max / 255;

        return $this;
    }

    /**
     * RGB转HSL
     *
     * @return $this
     *
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function rgb2hsl()
    {
        $rgb = [$this->red, $this->green, $this->blue];
        $max = max($rgb);
        $min = min($rgb);
        $diff = $max - $min;
        //  赋值
        if ($max == $min)
            $this->hue = 0;
        else if ($max == $this->red && $this->green >= $this->blue)
            $this->hue = 60 * (($this->green - $this->blue) / $diff);
        else if ($max == $this->red && $this->green < $this->blue)
            $this->hue = 60 * (($this->green - $this->blue) / $diff) + 360;
        else if ($max == $this->green)
            $this->hue = 60 * (($this->blue - $this->red) / $diff) + 120;
        else if ($max == $this->blue)
            $this->hue = 60 * (($this->red - $this->green) / $diff) + 240;
        $this->hue /= 360;
        $this->lightness = ($max + $min) / 2 / 255;
        $this->saturation = ((0 < $this->lightness && $this->lightness <= 0.5) ? $diff / (2 * $this->lightness) : ($this->lightness > 0.5) ? $diff / (2 - 2 * $this->lightness) : 0) / 255;

        return $this;
    }

    /**
     * RGB转HEX
     *
     * @return $this
     *
     * @copyright 魔网天创信息科技
     * @author    ComingDemon
     */
    public function rgb2hex()
    {
        $hexList = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F'];
        $rgb = [$this->red, $this->green, $this->blue];
        $hex = '';
        for ($i = 0; $i < 3; $i++) {
            $foo = null;
            $bar = $rgb[$i];
            $baz = [];
            while ($bar > 16) {
                $foo = $bar % 16;
                $bar = ($bar / 16) >> 0;
                array_push($baz, $hexList[$foo]);
            }
            array_push($baz, $hexList[$bar]);
            $hex .= str_pad(implode('', array_reverse($baz)), 2, '0', STR_PAD_LEFT);
        }
        //  赋值
        $this->hex = $hex;

        return $this;
    }

    /**
     * HSV转RGB
     *
     * @return $this
     *
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function hsv2rgb()
    {
        //  初始化
        $color = [0, 0, 0];
        $foo = floor($this->hue * 6);
        $bar = $this->hue * 6 - $foo;
        $baz = $this->value * (1 - $this->saturation);
        $qux = $this->value * (1 - $bar * $this->saturation);
        $nof = $this->value * (1 - (1 - $bar) * $this->saturation);
        switch ($foo % 6) {
            case 0:
                $color = [$this->value, $nof, $baz];
                break;
            case 1:
                $color = [$qux, $this->value, $baz];
                break;
            case 2:
                $color = [$baz, $this->value, $nof];
                break;
            case 3:
                $color = [$baz, $qux, $this->value];
                break;
            case 4:
                $color = [$nof, $baz, $this->value];
                break;
            case 5:
                $color = [$this->value, $baz, $qux];
                break;
        }
        //  循环处理
        foreach ($color as &$val)
            $val = (int)round($val * 255);
        //  赋值
        $this->red = $color[0];
        $this->green = $color[1];
        $this->blue = $color[2];

        return $this;
    }

    /**
     * HSV转HSL
     *
     * @return $this
     *
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function hsv2hsl()
    {
        $this->hsv2rgb();
        $this->rgb2hsl();

        return $this;
    }

    /**
     * HSV转HEX
     *
     * @return $this
     *
     * @copyright 魔网天创信息科技
     * @author    ComingDemon
     */
    public function hsv2hex()
    {
        $this->hsv2rgb();
        $this->rgb2hex();

        return $this;
    }

    /**
     * HSL转RGB
     *
     * @return $this
     *
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function hsl2rgb()
    {
        $_t = function($t) { return $t < 0 ? $t + 1 : ($t > 1 ? $t - 1 : $t); };
        $_c = function($c, $baz, $foo) { return (int)round(($c < (1 / 6) ? $baz + (($foo - $baz) * 6 * $c) : ((1 / 6) <= $c && $c < 0.5 ? $foo : (0.5 <= $c && $c < (2 / 3) ? $baz + (($foo - $baz) * 6 * (2 / 3 - $c)) : $baz))) * 255); };
        $foo = $this->lightness < 0.5 ? $this->lightness * (1 + $this->saturation) : $this->lightness + $this->saturation - ($this->lightness * $this->saturation);
        $bar = 2 * $this->lightness - $foo;
        $baz = $this->hue;
        $red = $_t($baz + (1 / 3));
        $green = $_t($baz);
        $blue = $_t($baz - (1 / 3));
        //  赋值
        $this->red = $_c($red, $bar, $foo);
        $this->green = $_c($green, $bar, $foo);
        $this->blue = $_c($blue, $bar, $foo);

        return $this;
    }

    /**
     * HSL转HSV
     *
     * @return $this
     *
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function hsl2hsv()
    {
        $this->hsl2rgb();
        $this->rgb2hsv();

        return $this;
    }

    /**
     * HSL转HEX
     *
     * @return $this
     *
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function hsl2hex()
    {
        $this->hsl2rgb();
        $this->rgb2hex();

        return $this;
    }

    /**
     * HEX转RGB
     *
     * @return $this
     *
     * @copyright 魔网天创信息科技
     * @author    ComingDemon
     */
    public function hex2rgb()
    {
        //  全写
        if (strlen($this->hex) > 3) {
            //  赋值
            $this->red = (int)round(hexdec(substr($color, 0, 2)));
            $this->green = (int)round(hexdec(substr($color, 2, 2)));
            $this->blue = (int)round(hexdec(substr($color, 4, 2)));
        }
        //  简写
        else {
            //  赋值
            $this->red = (int)round(hexdec(substr($this->hex, 0, 1) . substr($this->hex, 0, 1)));
            $this->green = (int)round(hexdec(substr($this->hex, 1, 1) . substr($this->hex, 1, 1)));
            $this->blue = (int)round(hexdec(substr($this->hex, 2, 1) . substr($this->hex, 2, 1)));
        }

        return $this;
    }

    /**
     * HEX转HSV
     *
     * @return $this
     *
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function hex2hsv()
    {
        $this->hex2rgb();
        $this->rgb2hsv();

        return $this;
    }

    /**
     * HEX转HSL
     *
     * @return $this
     *
     * @author    ComingDemon
     * @copyright 魔网天创信息科技
     */
    public function hex2hsl()
    {
        $this->hex2rgb();
        $this->rgb2hsl();

        return $this;
    }
}
