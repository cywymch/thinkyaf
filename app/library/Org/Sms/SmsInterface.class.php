<?php
/**
 * 短信接口
 */
namespace Org\Sms;

interface SmsInterface
{
    /**
     * 初始化配置
     * @param array $config
     * @return void
     */
    public function config($config);
    
    /**
     * 验证码发送
     * 
     * @param string $mobile
     * @param string $content
     * @return array
     */
    public function sms($mobile, $content);
}

