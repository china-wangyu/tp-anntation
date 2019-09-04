<?php
/** Created by 嗝嗝<china_wangyu@aliyun.com>. Date: 2019-09-03  */

namespace WangYu\annotation;


use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use WangYu\utils\Dir;
use WangYu\utils\File;
use WangYu\utils\Helper;

class TestCaseCommand extends Command
{
    protected $module;

    protected function configure()
    {
        parent::configure();
        $this->setName('annotation:test')
            ->addOption('module',null, Option::VALUE_REQUIRED, "your API Folder,Examples: api = /application/api", 'api')
            ->setDescription('Create a new resource controller class');
    }

    protected function execute(Input $input, Output $output)
    {
        try{
            $this->module = $input->getOption('module') ?? 'api';
            $apis = Helper::getApiAnnotation($this->module);
            $this->writeModule($apis);
            $output->writeln("Success. Please view it under the Unit Test Folder");
        }catch (\Exception $exception){
            $output->writeln("Error. Output phpunit Failed . error msg: " . $exception->getMessage());
        }
    }

    private function writeModule(array $apis = []):void {
        if (empty($apis))return;
        foreach ($apis as $api){
            $actions = $this->getActions($api);
            if(empty($actions)) continue;
            $this->writeController($api['class'],$actions);
        }
    }


    private function writeController(array $controller,string $actions):void {
        $testFileName = basename(str_replace("\\",'/',$controller['class']));
        $testFileNamespace = "tests\\".str_replace(["app\\","\\".$testFileName],'',$controller['class']);
        $testDir = str_replace("\\",'/',env("ROOT_PATH").$testFileNamespace);
        $testFile = $testDir.'/'.$testFileName."Test".'.php';
        $stub = $this->getStub();
        is_file($testFile) && unlink($testFile);
        !is_dir($testDir) && mkdir($testDir,0755,true);
        $content = str_replace(
            ["{%namespace%}","{%className%}","{%actions%}"],
            [$testFileNamespace,$testFileName."Test",$actions],
            file_get_contents($stub)
        );
        File::write($testFile,$content);
    }


    private function getActions(array $api = []):?string {
        if(empty($api))return '';
        $functionCode = '';
        foreach ($api['actions'] as $action){
            if (empty($action['action']) or empty($action['route']['method'])) continue;
            $name = $this->getActionName($action['action']);
            $route = $this->getActionRoute($api['class']['class'],$action['action']);
            $method = $this->getActionMethod($action['route']['method']);
            $params = $this->getActionParams($action);
            $stub = $this->getStub("action");
            $functionCode .= str_replace(
                    ["{%action%}","{%method%}","{%url%}","{%params%}"],
                    [ucfirst($name),$method,$route,$this->toString($params)],
                    file_get_contents($stub)
                ).PHP_EOL.PHP_EOL."     ";
        }
        return $functionCode;
    }

    private function getActionName(string $actionName):string {
        return $actionName;
    }

    private function getActionRoute(string $namespace,string $action):?string {
        $namespace = str_replace("app\\",'',$namespace);
        $namespace = str_replace("controller\\",'',$namespace);
        $namespaceArr = explode('\\',$namespace);
        if (isset($namespaceArr[2])){
            return $namespaceArr[0]."/".$namespaceArr[1].".".$namespaceArr[2]."/".$action;
        }
        return $namespaceArr[0]."/".$namespaceArr[1]."/".$action;
    }

    private function getActionMethod(string $method):?string{
        return strtolower($method);
    }

    private function getActionParams(array $action = []):?array {
        if (empty($action)) return [];
        if (!empty($action['validate'])){
            return $this->getActionValidateDefault($action['validate']);
        }
        return $this->getActionParamsDefault($action['param']);
    }

    private function getActionParamsDefault(array $params = []):?array {
        $responseData = [];
        if(empty($params))return [];

        if (isset($params[0])){
            foreach ($params as $param){
                if (empty($param['default'])) continue;
                $responseData[$param['name']] = $param['default'];
            }
        }else{
            !empty($params['default']) && $responseData[$params['name']] = $params['default'];
        }
        return $responseData;
    }

    private function getActionValidateDefault($validate):?array {
        $responseData = [];
        $validateFileMaps = Dir::getFiles($this->getValidateRootPath());
        $validateName = explode(".",$validate)[0];
        $validateFile = File::screen($validateFileMaps,$validateName);
        $validateModel = File::getObject($validateFile);
        if (method_exists($validateModel,'getDefault')){
            $responseData = $validateModel->getDefault();
        }
        return $responseData;
    }

    protected function getValidateRootPath(){
        $config_path = config('validate_dir');
        return $config_path ?? env('APP_PATH').$this->module.'/validate';
    }

    protected function getStub($type = "controller")
    {
        $stubPath = __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR;
        return $stubPath . $type.'.phpunit.stub';
    }

    private function toString(array $data = []):?string {
        $str = '[';
        foreach ($data as $key => $value){
            $str .=  '"'.$key.'"=>"'.$value.'", ';
        }
        $str = trim($str,', ');
        $str .= ']';
        return $str;
    }
}