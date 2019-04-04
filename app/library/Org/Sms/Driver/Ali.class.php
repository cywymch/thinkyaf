<?php
/**
 * 阿里大鱼验证码
 * 
 * @link https://github.com/aliyun/openapi-sdk-php-client?spm=a2c4g.11186623.2.17.17b43bb2lQzIdv
 */
namespace Org\Sms\Driver;
use Org\Sms\SmsInterface;

class Ali implements SmsInterface
{    
    /**
     * @var string
     */
    protected $sms_tpl;

    /**
     * @var array
     */
    public $config = [
        'product' => "Dysmsapi",
        'domain' => 'dysmsapi.aliyuncs.com',
        'sms_key' => '',
        'sms_secret' => '',
        'region' => 'cn-hangzhou',
        'endPointName' => 'cn-hangzhou'
    ];
    
    /**
     * 初始化配置
     * {@inheritDoc}
     * @see \Common\Org\Sms\SmsInterface::config()
     */
    public function config($config)
    {
        
    }
    
    /**
     * 发送验证码
     * {@inheritDoc}
     * @see \Common\Org\Sms\SmsInterface::sms()
     */
    public function sms($mobile, $content) 
    {

    }
}

