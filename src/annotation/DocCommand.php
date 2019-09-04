<?php
/**
 * Created by User: wene<china_wangyu@aliyun.com> Date: 2019/7/1
 */

namespace WangYu\annotation;

use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\Exception;
use WangYu\utils\Helper;

/**
 * Class Command tp命令行模式，输出API文档
 * @package WangYu
 */
class DocCommand extends \think\console\Command
{
    /**
     * @var string $file 文件
     */
    protected $file = '';
    /**
     * @var array $apis API反射数据
     */
    protected $apis = [];
    /**
     * @var string $ds 默认文档前缀
     */
    protected $dp = '#';
    /**
     * @var string $ds 默认文档后缀
     */
    protected $ds = PHP_EOL . PHP_EOL;

    protected function configure()
    {
        $this->setName('annotation:doc')
            ->addOption('module',null, Option::VALUE_REQUIRED, "your API Folder,Examples: api = /application/api", 'api')
            ->addOption('type',null, Option::VALUE_REQUIRED, "your API file type,type = html or markdown", 'html')
            ->addOption('name',null, Option::VALUE_REQUIRED, "your API filename", 'api-doc')
            ->addOption('force',null, Option::VALUE_REQUIRED, "your API filename is exist, backup and create, force = true or false", true)
            ->setDescription('Create API Doc');
    }

    /**
     * 执行函数
     * @param Input $input
     * @param Output $output
     * @throws exception\DocException
     */
    protected function execute(Input $input, Output $output)
    {
        try {
            $apis = [];
            $module = $input->getOption('module') ?? 'api';
            $type = $input->getOption('type') ?? 'html';
            $filename = $input->getOption('name') ?? 'api-doc';
            $force = $input->getOption('force') ?? true;
            $apis = Helper::getApiAnnotation($module);
            $className = '\WangYu\annotation\lib\Doc'.ucfirst($type);
            $doc = new $className($filename,$apis,$force);
            $doc->execute();
            $output->writeln("Successful. Output Document Successful . File Path ：$doc->file ");
        } catch (\Exception $exception) {
            $output->writeln("Error. Output Document Failed . error msg: " . $exception->getMessage());
        }
    }
}