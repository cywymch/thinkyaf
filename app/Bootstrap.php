<?php
use think\Db;

/**
 * Yaf启动程序
 * @author chen
 *
 */
class Bootstrap extends Yaf\Bootstrap_Abstract
{
    /**
     * 初始化配置
     */
    public function _initConfig(Yaf\Dispatcher $dispatcher)
    {
        $config = Yaf\Application::app()->getConfig();
        Yaf\Registry::set("config", $config);
        $dispatcher->getInstance()->disableView();
    }
    
    /**
     * 初始化session
     */
    public function _initSession()
    {
        Yaf\Session::getInstance()->start();
    }
    
    /**
     * 初始化数据连接
     */
    public function _initDb() 
    {
        $config = new Yaf\Config\Ini(__ROOT__."/conf/app.ini");
        $db = $config->get("db");
        $db_config = [
            'type'     => $db->type,
            'hostname' => $db->hostname,
            'database' => $db->database,
            'username' => $db->username,
            'password' => $db->password,
            'hostport' => $db->hostport,
            'charset'  => $db->charset,
            'prefix'   => $db->prefix,
            'debug'    => $db->debug
        ];
        Db::setConfig($db_config);
    }
    
    /**
     * 初始化视图
     * @param Yaf_Dispatcher $dispatcher
     */
    public function _initView(Yaf\Dispatcher $dispatcher)
    {
        //设置默认不渲染模板
        $dispatcher->getInstance()->disableView();
    }
    
    /**
     * 初始化默认模块
     * @param Yaf_Dispatcher $dispatcher
     */
    public function _initDefaultName(Yaf\Dispatcher $dispatcher)
    {
        $dispatcher->setDefaultModule("Index")->setDefaultController("Index")->setDefaultAction("index");
    }
}