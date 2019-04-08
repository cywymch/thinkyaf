<?php
/**
 * 互亿无线短信接口
 * 
 * @link http://www.ihuyi.com/demo/sms/php.html
 */
namespace Org\Sms\Driver;
use Org\Sms\Tools;
use Org\Sms\SmsInterface;
use Org\Sms\Request;

class Ihuyi implements SmsInterface
{
    /**
     * @var string
     */
    protected $sms_key;
    
    /**
     * @var string
     */
    protected $sms_secret;
    
    /**
     * @var string
     */
    private $sms_url = "https://106.ihuyi.com/webservice/sms.php?method=Submit";
    
    /**
     * 初始配置
     * {@inheritDoc}
     * @see \Common\Org\Sms\SmsInterface::config()
     */
    public function config(array $config):self 
    {
        $this->sms_key = $config['sms_key'];
        $this->sms_secret = $config['sms_secret'];
        return $this;
    }
    
    /**
     * 短信发送接口
     * {@inheritDoc}
     * @see \Common\Org\Sms\SmsInterface::sms()
     */
    public function sms(string $mobile, string $content):array
    {
        $sn = $this->sms_key;
        $password = $this->sms_secret;
        $post_data = "account=" . $sn . "&password=" . $password . "&mobile=" . $mobile . "&content=" . rawurlencode($content);
        // 密码可以使用明文密码或使用32位MD5加密
        $res = Tools::xml_to_array(Request::httpPost($this->sms_url, $post_data));
        $data = ['code' =>0, 'info'=>$res->data ];
        if ($res['code']!=2) {
            $data = ['code' =>1, 'msg'=>$res['SubmitResult']['msg']];
        }
        return $data;
    }
}

