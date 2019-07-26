<?php
/** Created By wene<china_wangyu@aliyun.com>, Data: 2019/7/16 */


namespace WangYu\exception;

use think\exception\Handle;
use think\facade\Log;

/**
 * Class TpApiException Thinkphp HTTP 异常基类扩展
 * @package WangYu\exception
 */
class TpHttpException extends Handle
{
    /**
     * 错误处理
     * @param \Exception $e
     * @return \think\Response|\think\response\Json
     */
    public function render(\Exception $e)
    {
        try {
            if (config('app_debug')) {
                return parent::render($e);
            } elseif (php_sapi_name() == 'cli') {
                return parent::render($e);
            } else {
                $result = $this->output(
                    $e->getMessage(),
                    method_exists($e, 'getUserCode') ? $e->getUserCode() : 1000,
                    empty($e->getCode()) ? 500 : $e->getCode()
                );
                $this->recordErrorLog($e);
            }
        } catch (\Exception $exception) {
            $result = $this->output($exception->getMessage(), 10000);
        }
        return json($result, $result['code'] ?? 500);
    }

    /**
     * 错误输出
     * @param string $msg
     * @param int $user_code
     * @param int $code
     * @return array
     */
    private function output(string $msg = '服務器內部錯誤，不想告訴你', int $user_code = 1000, int $code = 500)
    {
        return [
            'code' => $code ?? 500,
            'message' => $msg ?? '服務器內部錯誤，不想告訴你',
            'request_url' => request()->path() ?? ''
        ];
    }

    // 记录错误日志
    private function recordErrorLog(\Exception $e)
    {
        Log::init([
            'type' => 'File',
            'path' => '',
            'level' => ['error'],
            //单个日志文件的大小限制，超过后会自动记录到第二个文件
            'file_size' => 2097152,
            // 最大存储 30个文件
            'max_files' => 30,
            // json 格式记录
            'json' => true,
            'close' => false
        ]);
        Log::record($e->getMessage(), 'error');
    }
}