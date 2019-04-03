<?php
use Yaf\Controller_Abstract;
/**
 * 登录
 * @author chen <809783120@qq.com> 2018-03-11
 *
 */
class LoginController extends Controller_Abstract
{
    public function indexAction()
    {
        $this->display("index");
    }
    
    public function loginAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            header('Content-Type:application/json');
            $data['code'] = 0;
            $data['msg'] = '登录成功';
            json($data);
        }
    }
}