<?php
/** Created By wene<china_wangyu@aliyun.com>, Data: 2019/7/16 */


namespace WangYu\utils;


use WangYu\exception\utils\UtilsException;

trait File
{
    /**
     * 获取php类对象
     * @param $file
     * @return mixed
     * @throws UtilsException
     */
    static function getObject($file)
    {
        try {
            if (strstr($file, '.php') !== false) {
                return static::getThinkClass($file);
            }
            throw new \Exception('文件格式错误 . 当前格式为' . explode('.', basename($file))[1]);
        } catch (\Exception $exception) {
            throw new UtilsException('获取`.php`文件类实例对象失败 . ' . $exception->getMessage());
        }
    }

    /**
     * 获取thinkphp文件类
     * @param $file
     * @return mixed
     */
    static function getThinkClass($file){
        $namespace = str_replace(env('APP_PATH'), '/app/', $file);
        $namespace = str_replace('.php', '', $namespace);
        $namespace = str_replace('/', '\\', $namespace);
        $namespace = str_replace('\\\\', '\\', $namespace);
        if (class_exists($namespace)){
            return new $namespace();
        }
        return false;
    }


    /**
     * 返回对应文件
     * @param array $files
     * @param string $needle
     * @return string
     */
    static function screen(array $files, string $needle): string
    {

        foreach ($files as $file) {
            $new_file = str_replace('\\', '/', $file);
            if (strstr($needle, '.')) {
                $needle = str_replace('.', '/', $needle);
            }
            if (strstr($new_file, $needle) !== false) return $file;
            if (strstr($new_file, basename($needle)) !== false) return $file;
            continue;
        }
        return '';
    }


    /**
     * 备份文件
     * @param string $file
     * @throws \Exception
     */
    public static function backupFile(string $file): void
    {
        try {
            if (is_file($file)) {
                $newFile = dirname($file) . '/backup-' . date('YmdHis') . '-' . basename($file);
                if (!rename($file, $newFile)) throw new \Exception('备份文件失败~');
            }
        } catch (\Exception $exception) {
            throw new \Exception('备份文件失败~');
        }
    }

    /**
     * 写入数据
     * @param string $path 文件路径
     * @param string $data 文件数据
     * @param int $flags file_put_content flags参数
     * @return bool|int 返回数据 或 false
     */
    public static function write(string $path, string $data = '', $flags = FILE_APPEND | LOCK_EX)
    {
        return file_put_contents($path, $data, $flags);
    }


}