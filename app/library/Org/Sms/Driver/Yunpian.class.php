<?php
/**
 * 云片短信
 * 
 * @link https://github.com/yunpian/yunpian-php-sdk
 */
namespace Org\Sms\Driver;
use Org\Sms\SmsInterface;
use Yunpian\Sdk\YunpianClient;

class Yunpian implements SmsInterface
{
    /**
     * @var string
     */
    protected $sms_key;
    
    /**
     * @var string
     */
    protected $sign;

    /**
     * 初始配置
     * {@inheritDoc}
     * @see \Common\Org\Sms\SmsInterface::config()
     */
    public function config(array $config):self
    {
        $this->sms_key = $config['sms_key'];
        $this->sign = $config['sign'];
        return $this;
    }
    
    /**
     * 短信发送接口
     * {@inheritDoc}
     * @see \Common\Org\Sms\SmsInterface::sms()
     */
    public function sms(string $mobile,string $content):array
    {
        $data = array(
            'text'   =>"【".$this->sign."】".$content,
            'apikey' =>$this->sms_key,
            'mobile' =>$mobile
        );
        $client = YunpianClient::create($this->sms_key);
        $param = [
            YunpianClient::MOBILE=>$mobile,
            YunpianClient::TEXT=>$content
        ];
        $result = $client->sms()->single_send($param);
        $data = ['code' =>0, 'info'=>$result->data ];
        if (!$result->isSucc()) {
            $data = ['code' =>1, 'msg'=>$result->msg() ];
        }
        return $data;
    }
}

