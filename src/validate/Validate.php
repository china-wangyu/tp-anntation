<?php
/** Created By wene<china_wangyu@aliyun.com>, Data: 2019/7/18 */


namespace WangYu\validate;


class Validate extends \think\Validate
{
    /**
     * 设置验证参数默认值，在trr单元测试生产
     * @var array
     */
    protected $default = [
//        '参数名' => "参数默认值",
    ];

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