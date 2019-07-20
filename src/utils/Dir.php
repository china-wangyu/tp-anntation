<?php
/** Created By wene<china_wangyu@aliyun.com>, Data: 2019/7/16 */


namespace WangYu\utils;


use WangYu\utils\exception\UtilException;

trait Dir
{
    /**
     * 递归获取文件夹下的文件列表
     * @param string $dir 文件夹
     * @param string $ext 文件扩展名
     * @return array
     * @throws \Exception
     */
    public static function getFiles(string $dir,string $ext = '.php'):array
    {
        $files = [];
        try{
            if (empty($dir)) return $files;
            foreach (scandir($dir) as $index => $item){
                if (strstr($item,$ext) !== false){
                    array_push($files,$dir.'/'.$item);continue;
                }
                if (strstr($item,'.')!=false)  continue;
                $files = array_merge($files,static::getFiles($dir.'/'.$item,$ext));
            }
            return $files;
        }catch (\Exception $exception){
            throw new UtilException('获取文件夹下`'.$ext.'`文件失败 . '.$exception->getMessage());
        }
    }

    /**
     * 创建文件夹
     * @param string $dir 文件夹
     * @throws \Exception
     */
    public static function mkdir(string $dir):void
    {
        try{
            if(empty($path)) return;
            is_file($path) &&  $path = dirname($path);
            $res = mkdir($path, 0755, true);
            if ($res == false) throw new \Exception('文件夹权限不够');
        }catch (\Exception $exception){
            throw new UtilException('创建`'.$dir.'`文件夹失败 . '.$exception->getMessage());
        }
    }

}