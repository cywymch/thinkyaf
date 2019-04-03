<?php

class Request
{
    
    private static $header = array();
    
    private static $cookie = null;
    
    private function __construct()
    {}

    public static function post($url, $params = array())
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        $params_string = '';
        if (is_array($params)) {
            foreach ($params as $key => $vo) {
                $params_string .= $key . '=' . $vo.'&';
            }
        } else {
            $params_string = $params;
        }
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, self::$header);
        curl_setopt($curl, CURLOPT_COOKIE, self::$cookie);
        $output = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        return $output;
    }
    
    /**
     * 设置用户代理类型
     * 
     * @param string $agent
     * @return void
     */
    public static function setAgent(string $agent):void
    {
        self::$header['User-Agent'] = $agent;
        return;
    }
    
    /**
     * @param string $host
     * @return void
     */
    public static function setHost(string $host):void
    {
        self::$header['Host'] = $host;
        return;
    }
    
    public static function setCookie(string $cookie):void
    {
        self::$cookie = $cookie;
        return;
    }

    public static function get($url, $params = array())
    {
        $curl = curl_init();
        $params_string = '';
        if (is_array($params)) {
            foreach ($params as $key => $vo) {
                $params_string .= $key . '=' . $vo.'&';
            }
        } else {
            $params_string = $params;
        }
        curl_setopt($curl, CURLOPT_URL, $url.'?'.$params_string);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
       
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        return $output;
    }

    public static function put($url, $params = array())
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        $params_string = '';
        if (is_array($params)) {
            foreach ($params as $key => $vo) {
                $params_string .= $key . '=' . $vo.'&';
            }
        } else {
            $params_string = $params;
        }
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        return $output;
    }
    
    public static function delete($url, $params = array())
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        $params_string = '';
        if (is_array($params)) {
            foreach ($params as $key => $vo) {
                $params_string .= $key . '=' . $vo.'&';
            }
        } else {
            $params_string = $params;
        }
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        return $output;
    }
    
    /**
     * head请求
     * 
     * @param string $url
     * @param array $params
     * @return mixed
     */
    public static function head($url, $params = array())
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        $params_string = '';
        if (is_array($params)) {
            foreach ($params as $key => $vo) {
                $params_string .= $key . '=' . $vo.'&';
            }
        } else {
            $params_string = $params;
        }
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        return $output;
    }
}

