<?php
/**
 * 输出json
 * @param mixed   $data 返回的数据
 */
function json(array $data)
{
    header('Content-Type:application/json');
    echo json_encode($data);
    exit();
}

/**
 * 浏览器友好的变量输出
 * @param mixed         $var 变量
 * @param boolean       $echo 是否输出 默认为true 如果为false 则返回输出字符串
 * @param string        $label 标签 默认为空
 * @param integer       $flags htmlspecialchars flags
 * @return void|string
 */
function dump($var, $echo = true, $label = null, $flags = ENT_SUBSTITUTE)
{
    $label = (null === $label) ? '' : rtrim($label) . ':';
    ob_start();
    var_dump($var);
    $output = ob_get_clean();
    $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
    if (PHP_SAPI=='cli') {
        $output = PHP_EOL . $label . $output . PHP_EOL;
    } else {
        if (!extension_loaded('xdebug')) {
            $output = htmlspecialchars($output, $flags);
        }
        $output = '<pre>' . $label . $output . '</pre>';
    }
    if ($echo) {
        echo($output);
        return;
    } else {
        return $output;
    }
}

/**
 *  Cookie管理
 *  
 * @param string $name
 * @param mixed $value
 * @param array $option
 * @return array
 */
function cookie($name, $value = '', $option=[])
{
    if (is_null($value)) {
        Cookie::getInstance()->delete($name);
    }else if ($value ===''){
        return Cookie::getInstance()->get($name);
    }else {
        Cookie::getInstance()->set($name, $value, $option);
    }
}