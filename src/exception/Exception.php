<?php
/** Created By wene<china_wangyu@aliyun.com>, Data: 2019/7/16 */


namespace WangYu\exception;


use Throwable;

class Exception extends \Exception
{
    protected $message = '错误内容 . %s';
    protected $code = 400;
    protected $user_code = 1000;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            $this->getUserCode() . ' . ' . sprintf($this->message, $message, $this->getFile(), $this->getLine()),
            $code,
            $previous);
    }

    public function getUserCode(): int
    {
        return $this->user_code ?? 1000;
    }
}