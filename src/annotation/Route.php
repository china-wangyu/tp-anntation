<?php
/** Created By wene<china_wangyu@aliyun.com>, Data: 2019/7/16 */


namespace WangYu\annotation;

use think\App;
use think\facade\Route as Router;
use WangYu\exception\annotation\AnnotationException;
use WangYu\utils\Helper;

class Route extends Router
{
    /**
     * @var Route $instance
     */
    protected static $instance;

    public $data;

    protected static function getInstance(): self
    {
        if (!isset(static::$instance)) {
            return new static();
        }
        return static::$instance;
    }

    /**
     * 注释路由模块注册
     * @param string $module
     * @param array $middleware
     * @return Route
     * @throws AnnotationException
     */
    public static function reflex(string $module = 'api', $middleware = []): self
    {
        $_this = static::getInstance();
        try {
            foreach (Helper::getApiAnnotation($module) as $item) {
                $_this->setRoute($item, $middleware);
            }
            return $_this;
        } catch (\Exception $exception) {
            throw new AnnotationException($exception->getMessage());
        }
    }

    /**
     * 设置路由
     * @param array $api
     * @param array $middleware
     * @throws \Exception
     */
    protected function setRoute(array $api, array $middleware = [])
    {
        try {
            if (empty($api)) return;
            foreach ($api['actions'] as $key => $action) {
                if (empty($api['class']['group']) and empty($api['actions']['rule'])) continue;

                $this->set(
                    $this->setActionRule($api['class']['group'], $action['route']['rule']),
                    $this->setActionRoute($api['class']['class'], $action['action']),
                    $this->setActionMethod($action['route']['method']),
                    $this->setMiddleware($api['class']['middleware'], $action['middleware'], $middleware)
                );
            }
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * 设置中间件
     * @param array $clsMiddleware
     * @param array $funcMiddleware
     * @param array $routeMiddleware
     * @return array
     * @throws \Exception
     */
    protected function setMiddleware(array $clsMiddleware, array $funcMiddleware, array $routeMiddleware = []): array
    {
        try {
            // 设置类中间件
            if (!empty($clsMiddleware)){
                $routeMiddleware = empty($routeMiddleware) ? $clsMiddleware : array_merge($clsMiddleware, $routeMiddleware);
            }
            // 设置方法中间件
            if (!empty($funcMiddleware)) {
                $routeMiddleware = empty($routeMiddleware) ? $funcMiddleware : array_merge($funcMiddleware, $routeMiddleware);
            }
            return $routeMiddleware;
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * 获取方法路由规则
     * @param  $classRule
     * @param  $actionRule
     * @return string
     * @throws \Exception
     */
    protected function setActionRule($classRule, $actionRule): string
    {
        try {
            // 类路由和方法路由规则为空
            if (empty($classRule) and empty($actionRule)) return '';
            // 类路由不为空，方法路由为空
            if (!empty($classRule) and empty($actionRule)) return $classRule;
            // 类路由为空，方法路由不为空
            if (empty($classRule) and !empty($actionRule)) return $actionRule;
            // 都不为空的情况下，1.方法路由包含类路由规则
            if (strstr($actionRule, $classRule)) return $actionRule;
            // 都不为空的情况下，2.方法路由规则为根规则
            if (substr($actionRule, 0, 1) == '/') return $actionRule;
            // 都不为空的情况下，3. 拼接形成最后的规则
            return $classRule . '/' . $actionRule;

        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }

    }

    /**
     * 获取方法路由
     * @param string $class
     * @param string $action
     * @return string
     * @throws \Exception
     */
    protected function setActionRoute(string $class, string $action): string
    {
        try {
            $route = str_replace(env('APP_NAMESPACE') . '\\', '', $class);
            $route = str_replace('controller\\', '', $route);
            $route = explode('\\', $route);
            $route = $route[0] . '/' . $route[1] . (isset($route[2]) ? '.' . $route[2] : '');
            $route = $route . '/' . $action;
            return $route;
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * 设置方法路由
     * @param string $method
     * @return string
     */
    protected function setActionMethod(string $method): string
    {
        return $method;
    }

    /**
     * 设置方法路由
     * @param string $rule
     * @param string $route
     * @param string $method
     * @param array $middleware
     */
    public function set(string $rule, string $route, string $method, array $middleware = []): void
    {
        $this->data[$rule] = [
            'rule' => $rule,
            'route' => $route,
            'method' => $method,
            'middleware' => $middleware,
        ];
        Route::rule($rule, $route, $method)->middleware($middleware)->allowCrossDomain();
    }
}