<?php
/** Created By wene<china_wangyu@aliyun.com>, Data: 2019/7/16 */


namespace WangYu\annotation;

final class Annotation extends \WangYu\Reflex
{

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
        'route' => ['rule', 'method'], // 路由规则 @route('rule','method')
        'param' => ['name', 'doc', 'rule', 'default'], // 参数验证 @param('name','doc','rule','default')
        'validate' => 'validateModel', // 参数验证 @validate('validateModel')
        'middleware' => [], // 中间件注册函数 @middleware('中间件名称1','中间件名称2', ....)
        'success' => 'json', // 中间件注册函数 @middleware('中间件名称1','中间件名称2', ....)
        'error' => 'json', // 中间件注册函数 @middleware('中间件名称1','中间件名称2', ....)
    ];


    public function getClass(): array
    {
        $class['class'] = $this->rc->getName();
        foreach ($this->cls as $markName => $markKeys) {
            $class[$markName] = $this->get($markName, $markKeys);
        }
        return $class;
    }

    public function getAction()
    {
        $action['action'] = $this->rm->getName();
        foreach ($this->func as $markName => $markKeys) {
            $action[$markName] = $this->get($markName, $markKeys);
        }
        return $action;
    }

}