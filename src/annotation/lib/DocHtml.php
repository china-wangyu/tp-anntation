<?php
/** Created By wene<china_wangyu@aliyun.com>, Data: 2019/7/16 */
namespace WangYu\annotation\lib;


use WangYu\utils\Helper;

class DocHtml extends Doc
{
    protected $file_extension = '.html';

    protected function getFilePath()
    {
        // TODO: Implement getFilePath() method.
    }

    protected function writeHeader()
    {
        header("Content-Type:text/html;charset=utf-8");
        $header = $this->format( '<html>');
        $header .= $this->format('<header>');
        $header .= $this->format(' <meta name="viewport" content="width=device-width, initial-scale=1">');
        $header .= $this->format('<meta http-equiv="content-type" content="text/html; charset=UTF-8" />');
        $header .= $this->format('<meta http-equiv="content-language" content="zh-CN" />');
        $header .= $this->format('<link href="https://cdn.bootcss.com/jquery-jsonview/1.2.3/jquery.jsonview.min.css" rel="stylesheet">');
        $header .= $this->format('<link href="https://cdn.bootcss.com/github-markdown-css/3.0.1/github-markdown.css" rel="stylesheet">');
        $header .= $this->format('<script src="https://cdn.bootcss.com/jquery/3.4.1/jquery.min.js"></script>');
        $header .= $this->format('<style>
                                .markdown-body {
                                    box-sizing: border-box;
                                    min-width: 200px;
                                    max-width: 980px;
                                    margin: 0 auto;
                                    padding: 45px;
                                }
                            
                                @media (max-width: 767px) {
                                    .markdown-body {
                                        padding: 15px;
                                    }
                                }');
        $header .= $this->format('</style>');
        $header .= $this->format('</header>');
        $header .= $this->format('<body class=\'markdown-body\'>');
        $header .= $this->format('<h1>API Markdown 文档，源于<a href="https://github.com/china-wangyu/TRR">TRR</a>的美好生活💑。</h1>');

        $this->write($this->file,$header);
    }

    protected function writeToc()
    {
        $content = $this->format('<h2><code>TOC</code>目录</h2>');
        $content.= $this->format('<ul>');
        try {
            foreach ($this->apis as $api) {
                $content .= '<li>';
                $content .= '<p><a href="#'.$api['class']['class'].':'.
                    Helper::substr(empty($api['class']['doc']) ? '' : $api['class']['doc']).
                    '">'.
                    $api['class']['class'].':'.
                    Helper::substr(empty($api['class']['doc']) ? '' : $api['class']['doc']).'</a></p>';
                $content .= '<ul>';
                foreach ($api['actions'] as $action) {
                    $content .= '<p><a href="#'.$action['action'].':'.
                        Helper::substr(empty($action['doc']) ? '' : $action['doc']).
                        '">'.
                        $action['action'].':'.
                        Helper::substr(empty($action['doc']) ? '' : $action['doc']).'</a></p>';
                }
                $content .= '</ul>';
                $content .= '</li>';
            }
            $content.= $this->format('</ul>');
            $this->write($this->file, $content);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    protected function writeApi()
    {
        $content = $this->format('<h2><code>API</code>内容</h2>');
        try {
            foreach ($this->apis as $api) {
                $content .= $this->format('<h3><span id="'.$api['class']['class'].':'.
                    Helper::substr(empty($api['class']['doc']) ? '' : $api['class']['doc']).
                    '">'.
                    $api['class']['class'].':'.
                    Helper::substr(empty($api['class']['doc']) ? '' : $api['class']['doc']).'</span></h3>');
                foreach ($api['actions'] as $action) {
                    $content .= $this->format('<h4><span id="'.$action['action'].':'.
                        Helper::substr(empty($action['doc']) ? '' : $action['doc']).
                        '">'.
                        $action['action'].':'.
                        Helper::substr(empty($action['doc']) ? '' : $action['doc']).'</span></h4>');
                    $content .= $this->format($this->writeApiAction($api['class'],$action));
                }
            }
            $this->write($this->file, $content);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }



    protected function writeFooter()
    {
        $content = $this->format('<h1> 感谢🙏使用<a href="https://github.com/china-wangyu/TRR">TRR</a>，祝你生活美满～</h1>');
        $content .= $this->format('<script src="https://cdn.bootcss.com/jquery-jsonview/1.2.3/jquery.jsonview.min.js"></script>
            <script>
                //页面加载json格式化
                $(function () {
                    $(".language-json5").each(function (i) {
                        console.log($(this))
                        var json = $(this).html();
                        if (json != \'\' && json != \'undefined\'){
                            $(this).JSONView(json);
                        }
                    })
                });
            </script>');
        $this->write($this->file, $content);
    }


    private function writeApiAction(array $class ,array $action = []){
        $content = '<ul>';
        $content .= $this->getActionRoute(
            empty($class['group']) ? '' : ($class['group'] .'/'),
            empty($action['route']['rule']) ? '' : $action['route']['rule']
        );
        $content .= $this->getActionMethod($action['route']['method'] ?? '');
        $content .= $this->getActionParam($action['param']);
        $content .= $this->getActionSuccess($action['success'] ? json_encode(json_decode($action['success'],true)) : '');
        $content .= $this->getActionError($action['error'] ? json_encode(json_decode($action['error'],true)) :  '');
        $content .= '</ul>';
        return $content;
    }


    private function getActionRoute($group,$route){
        return '<li><p>[url] : <code>'.$group .$route .'</code></p></li>';
    }

    private function getActionMethod($method){
        return '<li><p>[method] : <code>'.$method.'</code></p></li>';
    }

    private function getActionParam(array $params = null){
        $content = '<li>';
        $content .= '<p>[params] : <code>请求参数文档</code></p>';
        $content .= '<table>';
        $content .= '<thead>
                    <tr>
                        <th style="text-align: center;">参数名称</th>
                        <th style="text-align: center;">参数文档</th>
                        <th style="text-align: center;">参数 <code>filter</code></th>
                        <th style="text-align: center;">参数默认</th>
                    </tr>
                </thead>';
        $content .= '<tbody>';
        if (isset($params[0])){
            foreach ($params as $param){
                $content .= '<tr>
                        <td style="text-align: center;">'.$param['name'].'</td>
                        <td style="text-align: center;">'.$param['doc'].'</td>
                        <td style="text-align: center;">'.$param['rule'].'</td>
                        <td style="text-align: center;">'.$param['default'].'</td>
                    </tr>';
            }
        }elseif (is_array($params) and !empty($params)){
            $content .= '<tr>
                        <td style="text-align: center;">'.$params['name'].'</td>
                        <td style="text-align: center;">'.$params['doc'].'</td>
                        <td style="text-align: center;">'.$params['rule'].'</td>
                        <td style="text-align: center;">'.$params['default'].'</td>
                    </tr>';
        }
        $content .= '</tbody>';
        $content .= '</table>';
        $content .= '</li>';
        return $content;
    }

    private function getActionSuccess($json){
        return $this->format('<p>[success] : <code>成功返回样例</code></p><pre><code id="result" class="language-json5">'.$json.'</code></pre>');
    }

    private function getActionError($json){
        return $this->format('<p>[error] : <code>失败返回样例</code></p><pre><code id="result" class="language-json5">'.$json.'</code></pre>');
    }

    /**
     * 格式化内容文档
     * @param string $content
     * @return string
     */
    protected function format(string $content = ''): string
    {
        return $content . PHP_EOL.PHP_EOL;
    }

}