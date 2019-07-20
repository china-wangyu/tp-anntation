<?php
/** Created By wene<china_wangyu@aliyun.com>, Data: 2019/7/16 */


namespace WangYu\annotation\lib;

use WangYu\annotation\Annotation;
use WangYu\exception\annotation\AnnotationException;
use WangYu\utils\File;
use WangYu\utils\Helper;

/**
 * Class ApiAnnotation API对象注解获取
 * @package WangYu\annotation\lib
 */
class ApiAnnotation
{

    /**
     * @var Annotation $annotation
     */
    public $annotation;

    /**
     * @var Helper $object
     */
    public $object;

    /**
     * @var array $data 返回数据
     */
    public $data = [] ;

    public function __construct(array $apiFile = []){
        try{
            if (empty($apiFile)) return $this->data;
            foreach ($apiFile as $file) {
                $this->object = File::getObject($file);
                $this->annotation = new Annotation($this->object);
                $this->data[get_class($this->object)] = $this->get();
            }
        }catch (\Exception $exception){
            throw new AnnotationException($exception->getMessage());
        }
    }

    /**
     * 获取API注解内容
     * @return array
     * @throws AnnotationException
     */
    protected function get():array {
        try{
            return [
                'class' => $this->getClass(),
                'actions' => $this->getActions(),
            ];
        }catch (\Exception $exception){
            throw new AnnotationException($exception->getMessage());
        }
    }

    protected function getClass():array {
        return $this->annotation->getClass();
    }

    protected function getActions():array {
        $methods = Helper::getMethods($this->object);
        if(empty($methods)) return [];
        foreach ($methods as &$method){
            $method = $this->getAction($method);
        }
        return $methods;
    }

    protected function getAction(string $method):array {
        $this->annotation->setMethod($method);
        return $this->annotation->getAction();
    }
}