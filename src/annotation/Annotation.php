<?php
/** Created By wene<china_wangyu@aliyun.com>, Data: 2019/7/16 */


namespace WangYu\annotation;

use WangYu\annotation\lib\AnnotationAnalyse;
use WangYu\exception\annotation\AnnotationException;

final class Annotation
{
    /**
     * @var \ReflectionClass $Reflection
     */
    protected $rc;

    /**
     * @var \ReflectionMethod
     */
    protected $rm;

    /**
     * @var AnnotationAnalyse $analyse 解析器
     */
    protected $analyse;

    /**
     * @var array $cls 类注解函数
     */
    protected $cls = [
        'doc' => 'doc', // 文档 @doc('notes')
        'group' => 'rule', // 路由分组 @group('rule')
        'middleware' => [], // 中间件注册函数 @middleware('中间件名称1','中间件名称2', ....)
    ];

    /**
     * @var array $func 方法注解函数
     */
    protected $func = [
        'doc' => 'doc', // 文档 @doc('notes')
        'route' => ['rule','method'], // 路由规则 @route('rule','method')
        'param' => ['name','doc','rule','default'], // 参数验证 @param('name','doc','rule','default')
        'validate' => 'validateModel', // 参数验证 @validate('validateModel')
        'middleware' => [], // 中间件注册函数 @middleware('中间件名称1','中间件名称2', ....)
        'success' => 'json', // 中间件注册函数 @middleware('中间件名称1','中间件名称2', ....)
        'error' => 'json', // 中间件注册函数 @middleware('中间件名称1','中间件名称2', ....)
    ];

    /**
     * Annotation constructor.
     * @param $object
     * @throws AnnotationException
     */
    public function __construct($object)
    {
        if (!is_object($object)) {
            throw new  AnnotationException('获取注解内容失败，参数要求对象，你给的是'.gettype($object));
        }
        try{
            $this->rc = new \ReflectionClass($object);
            $this->analyse = new AnnotationAnalyse($this->rc->getDocComment());
        }catch (\Exception $exception){
            throw new  AnnotationException(get_class($object).'类不存在');
        }
    }

    /**
     * 设置方法
     * @param string $method
     * @return Annotation
     * @throws AnnotationException
     */
    public function setMethod(string $method):self
    {
        try{
            $this->rm = $this->rc->getMethod($method);
            $this->analyse = new AnnotationAnalyse($this->rm->getDocComment());
            return $this;
        }catch (\Exception $exception){
            throw new  AnnotationException($this->rc->getName().'类不存在');
        }
    }


    public function getClass():array {
        $class['class'] = $this->rc->getName();
        foreach ($this->cls as $markName => $markKeys){
            $class[$markName] = $this->analyse->get($markName,$markKeys);
        }
        return $class;
    }

    public function getAction(){
        $action['action'] = $this->rm->getName();
        foreach ($this->func as $markName => $markKeys){
            $action[$markName] = $this->analyse->get($markName,$markKeys);
        }
        return $action;
    }
}