<?php
namespace Org\Sms\Driver;
use Org\Sms\SmsInterface;
use JiGuang\JSMS;

class JiGuang implements SmsInterface
{
    /**
     * 
     * @var string
     */
    protected $sms_key;
    
    /**
     * @var string
     */
    protected $sms_secret;
    
    /**
     * @var int
     */
    protected $template_id;
    
    /**
     * 初始配置
     * {@inheritDoc}
     * @see \Common\Org\Sms\SmsInterface::config()
     */
    public function config($config)
    {
        $this->sms_key = $config['sms_key'];
        $this->sms_secret = $config['sms_secret'];
        $this->template_id = $config['template_id'];
        return $this;
    }
    
    /**
     * 短信发送接口
     * {@inheritDoc}
     * @see \Common\Org\Sms\SmsInterface::sms()
     */
    public function sms($mobile, $content)
    {
        $sn = $this->sms_key;
        $password = $this->sms_secret;
        $client = new JSMS($this->sms_key, $this->sms_secret);
        $data= $client->sendMessage($mobile,$this->template_id,["code"=>$content]);
        $res = ['code' =>0, 'info'=>$data ];
        if ($data['http_code']!=200){
            $res['code'] = 1;
            $res['msg']= $data['body']['error']['message'];
        }
        return $res;
    }
}

