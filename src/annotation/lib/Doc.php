<?php
/** Created by 嗝嗝<china_wangyu@aliyun.com>. Date:   */

namespace WangYu\annotation\lib;


use WangYu\exception\annotation\AnnotationException;
use WangYu\utils\File;

abstract class Doc
{

    /**
     * @var string $file 文件
     */
    public $file = '';
    /**
     * @var array $apis API反射数据
     */
    public $apis = [];

    /**
     * @var string $file_extension 文件扩展名
     */
    protected $file_extension = '.html';

    /**
     * Doc constructor.
     * @param string $filename 文件名称
     * @param array $apis 接口数据
     * @param bool $force 是否备份
     * @throws \Exception
     */
    final public function __construct(string $filename,array $apis = [], bool $force = true)
    {
        $this->setFilePath($filename);
        $this->backupFile($force);
        $this->apis = $apis;
    }

    /**
     * 是否备份文件
     * @param bool $bool
     * @throws \Exception
     */
    protected function backupFile(bool $bool = true): void
    {
        $bool && File::backupFile($this->file);
    }

    /**
     * 执行
     * @throws AnnotationException
     */
    final public function execute()
    {
        try {
            $this->writeHeader();
            $this->writeToc();
            $this->writeApi();
            $this->writeFooter();
        } catch (\Exception $exception) {
            
            throw new AnnotationException('生成文档失败~，' . $exception->getMessage());
        }
    }
     protected function setFilePath(string $filename){
         $name = trim($filename);
         $name = $name ?: 'api-md-' . date('YmdHis');
         $this->file = env('ROOT_PATH') . $name . $this->file_extension;
     }

    /** 写头部 */
    abstract protected function writeHeader();

    /** 写导航 */
    abstract protected function writeToc();

    /** 写内容 */
    abstract protected function writeApi();

    /** 写底部内容 */
    abstract protected function writeFooter();


    /**
     * 写入数据
     * @param string $file 文件路径
     * @param string $content
     */
    final protected function write(string $file, string $content): void
    {
        File::write($file, $content);
    }
}