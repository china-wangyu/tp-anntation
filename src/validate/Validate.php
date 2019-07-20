<?php
/** Created By wene<china_wangyu@aliyun.com>, Data: 2019/7/18 */


namespace WangYu\validate;


use WangYu\exception\validate\ValidateException;

class Validate extends \think\Validate
{
    /**
     * @return bool
     * @throws ValidateException
     */
    public function goCheck()
    {
        //获取HTTP传入的参数
        $params = request()->param();
        //对这些参数做校验
        $result = $this->batch()->check($params);
        if (!$result) {
            throw new ValidateException(explode(', ',$this->error));
        } else {
            return true;
        }
    }

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