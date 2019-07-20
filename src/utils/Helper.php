<?php
/** Created By wene<china_wangyu@aliyun.com>, Data: 2019/7/18 */


namespace WangYu\utils;


use WangYu\annotation\lib\ApiAnnotation;
use WangYu\utils\exception\UtilException;

trait Helper
{
    /**
     * 获取对象自身方法，去除父类方法
     * @param object $object
     * @return array
     * @throws \Exception
     */
    static function getMethods($object){
        try{
            if (!is_object($object)) throw new \Exception('$object 参数期望的值为 object 类型');
            $parentActions = get_class_methods(get_parent_class($object));
            $objectActions = get_class_methods($object);
            if (empty($parentActions)) return $objectActions;
            $actions = array_diff($objectActions, $parentActions);
            foreach ($actions as $key => $action){
                if (strstr($action,'__') !== false) {
                    unset($actions[$key]);
                }
            }
            return empty($actions) ? [] : array_values($actions);
        }catch (\Exception $exception){
            throw new UtilException($exception->getMessage());
        }
    }

    /**
     * 获取Api类注解内容
     * @param string $module 模块名，对应TP项目application下的某个目录
     * @return \Generator
     * @throws \WangYu\exception\annotation\AnnotationException
     */
    static function getApiAnnotation(string $module)
    {
        $apiFiles = Dir::getFiles(env('APP_PATH').'/'.$module.'/'.config('url_controller_layer'));
        $apiAnnotations = new ApiAnnotation($apiFiles);
        foreach ($apiAnnotations->data as $api){
            yield $api;
        }
    }

    /**
     * 截取字符串
     * @param $content
     * @param int $start
     * @param int $number
     * @return string
     */
    public static function substr($content,int $start = 0, int $number = 40)
    {
        return strtolower(substr(str_replace(' ','.',trim($content)),$start,$number));
    }
}