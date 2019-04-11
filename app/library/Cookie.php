<?php
/**
 * cookie
 * @author chen
 *
 */
class Cookie
{
    /**
     * 配置参数
     * @var array
     */
    protected static $config = [
        // cookie 名称前缀
        'prefix'   => '',
        // cookie 保存时间
        'expire'   => 0,
        // cookie 保存路径
        'path'     => '/',
        // cookie 有效域名
        'domain'   => '',
        //  cookie 启用安全传输
        'secure'   => false,
        // httponly设置
        'httponly' => false,
    ];
    
    /**
     * Cookie写入数据
     * @var array
     */
    protected static $cookie = [];
    
    /**
     * @var self
     */
    protected static $instance;
    
    private function __construct() {}
    
    private function __clone() {}
    
    /**
     * 获取对象实例
     * @param array $config
     * @return self
     */
    public static function getInstance(array $config = []):self
    {
        $config = new Yaf\Config\Ini(__ROOT__."/conf/app.ini");
        $config_array = $config->toArray();
        $cookie = $config_array["cookie"];
        if ($cookie) {
            static::$config = array_merge(static::$config, $cookie);
        }
        if (! empty ( static::$config['httponly'] )) {
            ini_set ( "session.cookie_httponly", 1 );
        }
        if (! (self::$instance instanceof self)) {
            self::$instance = new self();
        }
        self::$cookie = $_COOKIE;
        return self::$instance;
    }
    
    /**
     * 获取cookie保存数据
     * @return mixed
     */
    public function get(string $name = '')
    {
        if ($name === '') {
            return self::$cookie;
        }else {
            return self::$cookie[$name];
        }
    }
    
    /**
     * 设置cookie
     * 
     * @param string $name
     * @param string $value
     * @param array $option
     * @return void
     */
    public function set(string $name, string $value, array $option=[]):void
    {
        if (!empty($option)) {
            $config = array_merge(static::$config,$option);
        }else {
            $config = static::$config;
        }
        $expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;
        $prefix = $config['prefix'];
        $res = setcookie($prefix.$name, $value, $expire, $config['path'], $config['domain'], $config['secure'] ? true : false, $config['httponly'] ? true : false);
    }
    
    /**
     * 
     * 设置或者获取cookie作用域（前缀）
     * @access public
     * @param  string $prefix
     * @return string|void
     */
    public function prefix(string $prefix='')
    {
        if (empty($prefix)) {
            return static::$config['prefix'];
        }
        static::$config['prefix'] = $prefix;
    }
    
    /**
     * Cookie删除
     * @access public
     * @param  string      $name cookie名称
     * @param  string|null $prefix cookie前缀
     * @return void
     */
    public function delete(string $name, string $prefix = null): void
    {
        $prefix = $prefix ?: static::$config['prefix'];
        setcookie($prefix . $name, '', time() - 3600);
    }
}

