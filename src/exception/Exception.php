<?php
/** Created By wene<china_wangyu@aliyun.com>, Data: 2019/7/16 */


namespace WangYu\exception;


use Throwable;

class Exception extends \Exception
{
    protected $message = '系统内部错误';
    protected $code = 400;
    protected $user_code = 1000;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            empty($message) ?$this->message : $message,
            empty($code) ?$this->code : $code,
            $previous
        );
    }

    public function getUserCode(): int
    {
        return $this->user_code ?? 1000;
    }
}