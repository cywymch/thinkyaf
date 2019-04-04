<?php
/**
 * 验证码发送
 */
namespace Org\Sms;

class Sms
{

    /**
     *
     * @var object
     */
    protected static $smsObj;

    /**
     *
     * @var array
     */
    public static $config = [
        'sms_key' => '',
        'sms_secret' => '',
        'sign' => ''
    ];

    /**
     *
     * @var object
     */
    protected static $instance;

    /**
     * Construct
     */
    private function __construct()
    {}

    /**
     * Clone
     */
    private function __clone()
    {}

    /**
     * 获取短信对象实例
     *
     * @param string $type
     *            短信类型
     * @return \Common\Org\Sms\Sms
     */
    public static function getInstance()
    {
        if (! (self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 初始配置
     * config 说明.
     * <ul>
     *
     * <li>-sms_key: app_id</li>
     * <li>-sms_secret: app_key</li>
     * <li>-template_id: 模板id</li>
     * <li>-sign: 签名</li>
     * <li>-type: 短信类型 ['Yunpian','JiGuang','Ihuyi']</li>
     * </ul>
     * 
     * @param array $config            
     * @return self
     */
    public function config($config = [])
    {
        if (C('sms')) {
            static::$config = array_merge(static::$config, C('sms'));
        }
        if ($config) {
            static::$config = array_merge(static::$config, $config);
        }
        $type = static::$config['type'];
        $class = __NAMESPACE__ . "\Driver\\" . $type;
        self::$smsObj = new $class();
        return $this;
    }

    /**
     * 发送验证码
     *
     * @param string $mobile
     *            手机号
     * @param string $content
     *            短信模板|验证码
     * @param
     *            array
     */
    public function send($mobile, $content)
    {
        return self::$smsObj->config(self::$config)->sms($mobile, $content);
    }
}

