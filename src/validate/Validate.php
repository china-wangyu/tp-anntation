<?php
/** Created By wene<china_wangyu@aliyun.com>, Data: 2019/7/18 */


namespace WangYu\validate;


class Validate extends \think\Validate
{
    public function getField(){
        return $this->field;
    }

    public function getRule(){
        return $this->rule;
    }

    public function getMessage(){
        return $this->message;
    }
}