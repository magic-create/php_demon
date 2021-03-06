# PHP Demon Library

## 框架说明

正常来说是内部开发使用的，外部使用也可以（水平有限，请慎用，可能会有漏洞或者性能问题）

## 包含范围

| 类型 | 说明                                              | 例子                                            |
| ---- | ------------------------------------------------- | ----------------------------------------------- |
| 转换 | 数组、对象、JSON、XML、参数的转换过滤以及相关内容 | bomber()->jsonToXml()<br>array_to_object([])    |
| 请求 | 根据请求来判断各项内容，如是否为手机版、获取URL等 | bomber()->url()<br>bomber()->isMobile()         |
| 文件 | 递归创建目录、创建文件、上传、下载等              | bomber()->dirMake()<br>bomber()->fileCreate()   |
| 图片 | 基础的缩略图、裁剪、水印、转换等                  | bomber()->imageThumb()<br>bomber()->imageMark() |
| 正则 | 提供几个常见的正则                                | bomber()->regexp()                              |
| 加密 | 简单的盐值加密、异或加密等                        | bomber()->password()                            |
| RSA  | 简单的RSA封装使用                                 | $rsa = (new Rsa())                              |

## Helper函数

| 名称    | 说明                                            | 例子                                |
| ------- | ----------------------------------------------- | ----------------------------------- |
| debuger | 调试输出并中断                                  | debuger('1','2',3,[])               |
| dumper  | 和debuger相同但是不中断                         | dumper('1','2',3,[])                |
| mstime  | 获取当前运行毫秒时间戳                          | mstime()                            |
| msdate  | 将毫秒时间戳转换为自定义日期格式                | msdate('Ymd',mstime())              |
| bomber  | 通用函数类快捷函数                              | bomber()->xxx()                     |
| arguer  | 常用的参数过滤，如从$_REQUEST获取或者自定义数组 | arguer('a','无','string',['a'=>'']) |

## 特殊申明

本库已发布至Composer，理论上只内部使用，如有问题请自行解决，概不提供服务

最终解释权归魔网天创信息科技:尘兵所属
