<?php
/** Created By wene<china_wangyu@aliyun.com>, Data: 2019/7/16 */


namespace WangYu\exception\utils;


use WangYu\exception\Exception;

class UtilException extends Exception
{
    protected $user_code = 1003; // 工具箱报错code，从9000起
}