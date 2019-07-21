<?php
/** Created By wene<china_wangyu@aliyun.com>, Data: 2019/7/16 */


namespace WangYu\annotation;

use WangYu\exception\annotation\AnnotationException;
use WangYu\exception\validate\ValidateException;
use WangYu\utils\Dir;
use WangYu\utils\File;

/**
 * Class Validate 检验路由的参数
 * @package WangYu\annotation
 */
class Validate
{
    /**
     * @var \think\Request $request
     */
    protected $request;

    protected $annotation;

    protected $rule;

    protected $field;

    /**
     * 权限验证
     * @param \think\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws AnnotationException
     */
    public function handle(\think\Request $request, \Closure $next)
    {
        try{
            $this->request = $request;
            $this->setAnnotation();
            $this->setRule();
            $res = $this->goCheck();
            if (!$res) {
                throw new \Exception('参数验证 .   '.join(',',$this->rule->getError()));
            }
            return $next($request);
        }catch (\Exception $exception){
            throw new ValidateException($exception->getMessage());
        }

    }

    /**
     * 设置注解模型
     * @param \think\Request $request
     * @throws AnnotationException
     */
    protected function setAnnotation()
    {
        $controller = lcfirst(str_replace('.',DIRECTORY_SEPARATOR,$this->request->controller()));
        $class = env('APP_NAMESPACE').DIRECTORY_SEPARATOR.$this->request->module().DIRECTORY_SEPARATOR.
            config('url_controller_layer').DIRECTORY_SEPARATOR.$controller;
        $class = str_replace('/','\\',$class);
        $this->annotation = (new Annotation(new $class()))->setMethod($this->request->action())->getAction();
    }


    protected function setRule(){
        if (empty($this->annotation['validate'])){
            $this->setParamRule();
        }else{
            $this->setValidateRule();
        }
    }

    protected function setValidateRule(){
        try{
            $validateFileMaps = Dir::getFiles($this->getValidateRootPath());
            $validateFile = File::screen($validateFileMaps,$this->request->controller().'.'.$this->annotation['validate']);
            $this->rule = File::getObject($validateFile);
            if ($this->rule == '') throw new \Exception("注解验证器错误. @validate('$this->annotation['validate']')不存在");
        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
        }
    }


    protected function getValidateRootPath(){
        $config_path = config('validate_dir');
        return $config_path ?? env('APP_PATH').$this->request->module().'/validate';
    }

    protected function setParamRule(){
        try{
            foreach ($this->annotation['param'] as $item){
                if(empty($item['rule'])) continue;
                $this->field[$item['name']] = $item['doc'];
                $this->rule[$item['name']] = $item['rule'];
            }
        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
        }
    }

    // 执行检验
    protected function goCheck(){
        try{
            if(empty($this->rule)) return true;
            if (is_array($this->rule)) {
                $this->rule = (new \think\Validate())->make($this->rule,[],$this->field);
            }
            if ($this->rule instanceof  \think\Validate){
                return $this->rule->batch()->check($this->request->param());
            }
            return true;
        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
        }
    }

}