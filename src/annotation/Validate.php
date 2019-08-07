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

    protected $scene;

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
        $this->request = $request;
        $this->setAnnotation();
        if (!empty($this->annotation['param']) or !empty($this->annotation['validate'])){
            $this->setRule();
            $res = $this->goCheck();

            if (!$res) {
                throw new ValidateException(join(',',$this->rule->getError()));
            }
        }
        return $next($request);

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
            $this->setValidateScene();
            $validateFile = File::screen($validateFileMaps,$this->request->controller().'.'.$this->annotation['validate']);
            $this->rule = File::getObject($validateFile);
            if ($this->rule == '') throw new \Exception("注解验证器错误. @validate('$this->annotation['validate']')不存在");
        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
        }
    }

    protected function setValidateScene(){
        if (strstr($this->annotation['validate'],'.')){
            $validateArr = explode('.',$this->annotation['validate']);
            $this->scene = $validateArr[1] ?? null;
            $this->annotation['validate'] = $validateArr[0];
        }
    }

    protected function getValidateRootPath(){
        $config_path = config('validate_dir');
        return $config_path ?? env('APP_PATH').$this->request->module().'/validate';
    }

    protected function setParamRule(){
        try{
            if (isset($this->annotation['param'][0])){
                foreach ($this->annotation['param'] as $item){
                    if(empty($item['rule'])) continue;
                    $this->field[$item['name']] = $item['doc'];
                    $this->rule[$item['name']] = $item['rule'];
                }
            }else{
                if(empty($this->annotation['param']['rule'])) return;
                $this->field[$this->annotation['param']['name']] = $this->annotation['param']['doc'];
                $this->rule[$this->annotation['param']['name']] = $this->annotation['param']['rule'];
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
                if (empty($this->scene)){
                    return $this->rule->batch()->check($this->request->param());
                }else{
                    return $this->rule->scene($this->scene)->batch()->check($this->request->param());
                }
            }
            return true;
        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
        }
    }

}