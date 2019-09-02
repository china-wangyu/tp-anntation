<?php
/** Created By wene<china_wangyu@aliyun.com>, Data: 2019/7/16 */

namespace WangYu\annotation\lib;


use WangYu\exception\annotation\AnnotationException;
use WangYu\utils\File;
use WangYu\utils\Helper;

class DocMarkdown extends Doc
{
    protected $file_extension = '.md';
    
    /**
     * @var string $ds 默认文档前缀
     */
    protected $dp = '#';
    /**
     * @var string $ds 默认文档后缀
     */
    protected $ds = PHP_EOL . PHP_EOL;

    protected function getFilePath()
    {
        // TODO: Implement getFilePath() method.
    }
    
    protected function writeHeader()
    {
        // TODO: Implement writeHeader() method.
        $content = $this->format(' API Markdown 文档，源于[TRR](https://github.com/china-wangyu/TRR)的美好生活💑。');
        $this->write($this->file, $content);
    }

    /**
     * 写TOC文档
     */
    protected function writeToc(): void
    {
        $content = $this->format('# `TOC`目录');
        try {
            foreach ($this->apis as $api) {
                $this->dp = '- ';
                $content .= $this->formatToc(
                    Helper::substr($api['class']['class']) . ':' .
                    Helper::substr(empty($api['class']['doc']) ? '' : $api['class']['doc'])
                );
                foreach ($api['actions'] as $action) {
                    $this->dp = '   - ';
                    $content .= $this->formatToc(
                        Helper::substr($action['action']) . ':' .
                        Helper::substr(empty($action['doc']) ? '' : $action['doc'])
                    );
                }
            }
            $this->write($this->file, $content);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }


    /**
     * 写API文档
     */
    protected function writeApi(): void
    {
        try {
            $this->dp = '## ';
            $content = $this->format(' `API`内容');
            foreach ($this->apis as $api) {
                $this->dp = '### ';
                $content .= $this->formatToc(
                    Helper::substr($api['class']['class']) . ':' .
                    Helper::substr(empty($api['class']['doc']) ? '' : $api['class']['doc'])
                );
                foreach ($api['actions'] as $action) {
                    $content .= $this->writeAction($api, $action);
                }
            }
            $this->write($this->file, $content);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    protected function writeFooter()
    {
        $content = $this->format(' 感谢🙏使用[TRR](https://github.com/china-wangyu/TRR)，祝你生活美满～');
        $this->write($this->file, $content);
    }

    /**
     * 写入方法
     * @param array $action
     * @return string
     * @throws \Exception
     */
    protected function writeAction(array $api, array $action = []): string
    {
        try {
            $this->dp = '#### ';
            $content = $this->writeDoc(
                $action['action'],
                empty($action['doc']) ? '' : $action['doc']
            );
            $this->dp = '- ';
            $content .= $this->writeUrl(
                empty($api['class']['group']) ? '' : $api['class']['group'],
                empty($action['route']['rule']) ? '' : $action['route']['rule']
            );
            $content .= $this->writeMethod(empty($action['route']['method']) ? '*' : $action['route']['method']);
            $content .= $this->writeParam($action['param']);
            $content .= $this->writeSuccess($action['success']);
            $content .= $this->writeError($action['error']);
            return $content;
        } catch (\Exception $exception) {
            throw new \Exception($api['class']['class'] . ' . ' . $action['action'] . ' . ' . $exception->getMessage());
        }
    }

    // 写方法注释
    protected function writeDoc($action, $doc)
    {
        return $this->format(Helper::substr($action) . ':' . Helper::substr($doc));
    }

    // 写方法URL
    protected function writeUrl($route, $rule)
    {
        return $this->format('[url] : `' .
            (empty($route) ? '' : '/' . $route) .
            (empty($rule) ? '`' : '/' . $rule . '`')
        );
    }

    // 写方法请求类型
    protected function writeMethod($method)
    {
        return $this->format('[method] : `' . $method . '`');
    }

    // 写方法请求参数
    protected function writeParam($params)
    {
        try {
            $content = $this->format('[params] : `请求参数文档`');
            $this->dp = '';
            $this->ds = PHP_EOL;
            $content .= $this->format('   | 参数名称 | 参数文档 | 参数 `filter` | 参数默认 |');
            $content .= $this->format('   | :----: | :----: | :----: | :----: |');
            if (is_array($params) and isset($params[0])){
                foreach ($params as $param) {
                    $content .= $this->format(
                        '   | ' . $param['name'] .
                        ' | ' . $param['doc'] . ' | ' .
                        str_replace('|', '#', $param['rule']) .
                        ' | ' . $param['default'] . ' |'
                    );
                }
            }else if(is_array($params) and !empty($params)){
                $content .= $this->format(
                    '   | ' . $params['name'] .
                    ' | ' . $params['doc'] . ' | ' .
                    str_replace('|', '#', $params['rule']) .
                    ' | ' . $params['default'] . ' |'
                );
            }
            $this->ds = PHP_EOL . PHP_EOL;
            return $content;
        } catch (\Exception $exception) {
            throw new \Exception('@param()注解函数内容有误请检查 . 报错原因：' . $exception->getMessage());
        }
    }

    // 写方法错误返回
    protected function writeError($json)
    {
        $json = empty($json) ? '' : $json;
        $content = $this->format('- [error] : `错误返回样例`');
        $content .= $this->format('   ```json5' . PHP_EOL . '    ' . $json . PHP_EOL . '   ```');
        return $content;
    }

    // 写方法正确返回
    protected function writeSuccess($json)
    {
        $json = empty($json) ? '' : $json;
        $content = $this->format('- [success] : `成功返回样例`');
        $content .= $this->format('   ```json5' . PHP_EOL . '    ' . $json . PHP_EOL . '   ```');
        return $content;
    }

    /**
     * 格式化内容文档
     * @param string $content
     * @return string
     */
    protected function format(string $content = ''): string
    {
        return $this->dp . $content . $this->ds;
    }


    /**
     * 获取Toc内容文档
     * @param string $content
     * @return string
     */
    protected function formatToc(string $content = ''): string
    {
        return $this->dp . ' [' . ucwords($content) . '](#' . $content . ')' . $this->ds;
    }
}