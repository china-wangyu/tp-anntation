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
use WangYu\annotation\lib\DocHtml;
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
        $this->setName('doc:build')
            ->addArgument('module', Argument::OPTIONAL, "your API Folder,Examples: api = /application/api", 'api')
            ->addArgument('type', Argument::OPTIONAL, "your API file type,type = html or markdown", 'html')
            ->addArgument('filename', Argument::OPTIONAL, "your API to markdown filename", 'api-md')
            ->addArgument('force', Argument::OPTIONAL, "your API markdown filename is exist, backup and create", true)
            ->setDescription('API to Markdown');
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
            foreach(Helper::getApiAnnotation($input->getArgument('module')) as $item){
                array_push($apis,$item);
            }
            $className = '\WangYu\annotation\lib\Doc'.ucfirst($input->getArgument('type'));
            $doc = new $className($input->getArgument('filename'),$apis,$input->getArgument('force'));
            $doc->execute();
            $output->writeln("Successful. Output Document Successful . File Path ：$doc->file ");
        } catch (\Exception $exception) {
            $output->writeln("Error. Output Document Failed . error msg: " . $exception->getMessage());
        }
    }
}