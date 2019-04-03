<?php
/**
 * Elasticsearch
 * @author 陈英 <809783120@qq.com> 2017-11-29
 *
 */
class Elasticsearch
{

    private $config = [
        'host' => 'localhost',
        'port' => '9200'
    ];
    
    public $host = null;
    
    public function __construct($config = array())
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 设置连接地址
     *
     * @param string $params            
     * @return string
     */
    public function setHost(string $params=''): void
    {
        if (empty($this->config)) {
            $this->host = 'http://' . $params . '/';
        } else {
            $this->host = 'http://' . $this->config['host'] . ':' . $this->config['port'] . '/';
        }
    }

    /**
     * 获取索引信息
     * @param string $index  索引名称
     * @return array
     */
    public function getIndex(string $index): array
    {
        $url = $this->host . $index;
        $data = Request::get($url);
        return json_decode($data, true);
    }

    /**
     * 创建索引
     *
     * @param string $index     索引名称
     * @param int $shards       分片数量 默认5
     * @param int $replicas     副本数量 1
     * @author 陈英 <809783120@qq.com> 2017-11-29
     * @return array
     */
    public function createIndex(string $index, int $shards = 5, int $replicas = 0): array
    {
        $url = $this->host . $index ;
        $params = [
            'settings' => [
                'index' => [
                    'number_of_shards' => $shards,
                    'number_of_replicas' => $replicas
                ]
            ]
        ];
        $data = Request::put($url, json_encode($params));
        return json_decode($data, true);
    }
    
    /**
     * 删除索引
     * 
     * @param string $index  索引名称
     * @param bool $all  是否删除所有索引
     * @return array
     */
    public function deleteIndex(string $index, bool $all): array
    {
        if ($all) {
            $url = $this->host . '*';
        } else {
            $url = $this->host . $index;
        }
        $data = json_decode(Request::delete($url), true);
        return $data;
    }

    /**
     * 更新索引配置
     * 
     * @param string $index 索引名称
     * @param array $params  参数
     * @return array
     */
    public function updateSetting(string $index, array $params): array
    {
        $url = $this->host . $index . '/_settings';
        return json_decode(Request::put($url, $params), true);
    }
    
    /**
     * 获取索引配置
     * 
     * @param string $index
     * @return array
     */
    public function getSetting(string $index): array
    {
        $url = $this->host . $index . '/_settings';
        return json_decode(Request::get($url), true);
    }

    /**
     * 设置索引映射
     * 
     * @param string $index      索引名称
     * @param string $doc_name   文档类型
     * @param array $fields      文档字段 ['fields_name'=>['type'=>'int']]
     * @return array
     */
    public function setMapping(string $index, string $doc_name, array $fields): array
    {
        $url = $this->host . $index;
        $params['mappings'] = [
            $doc_name => [
                'properties' => $fields
            ]
        ];
        $data = json_decode(Request::put($url, $params), true);
        return $data;
    }
    
    /**
     * 更新索引映射
     *
     * @param string $index      索引名称
     * @param string $doc_name   文档类型
     * @param array $fields      文档字段 ['fields_name'=>['type'=>'int']]
     * @return array
     */
    public function upateMapping(string $index, string $doc_name,array $fields): array
    {
        $url = $this->host . $index .'/_mapping/'.$doc_name;
        $params['properties'] =  $fields ;
        $data = json_decode(Request::put($url, $params), true);
        return $data;
    }
    
    /**
     * 合并索引
     * 
     * @param string $index  索引名称
     * @return array
     */
    public function forcemerge(string $index): array
    {
        $url = $this->host . $index . '/_forcemerge';
        $data = json_decode(Request::post($url), true);
        return $data;
    }

    /**
     * 清理缓存
     * 
     * @param string $index  索引名称
     * @return array
     */
    public function clear(string $index): array
    {
        $url = $this->host . $index . "/_cache/clear";
        $data = json_decode(Request::post($url), true);
        return $data;
    }
    
    /**
     * 刷新索引
     * 
     * @param string $index 索引名称
     * @return array
     */
    public function refresh(string $index): array
    {
        $url = $this->host . $index . "/_refresh";
        $data = json_decode(Request::post($url), true);
        return $data;
    }
    
    /**
     * 冲洗索引
     * 
     * @param string $index  索引名称
     * @return array
     */
    public function flush(string $index): array
    {
        $url = $this->host . $index . "/_flush";
        $data = json_decode(Request::post($url), true);
        return $data;
    }
    
    /**
     * 判断文档类型是否存
     * 
     * @param string $index  索引名称
     * @param string $doc    文档类型
     * @return int   200 存在 404 不存在
     */
    public function doc_exist(string $index, string $doc):int
    {
        $url = $this->host . $index .'/'.$doc;
        $data = Request::head($url);
        return $data;
    }

    /**
     * 新增文档
     * 
     * @param string $index  索引名称
     * @param string $doc    文档名称
     * @param int $id        文档id
     * @param array $params  文档  ['fields_name'=>'data']
     * @return array
     */
    public function add_doc(string $index, string $doc, int $id, array $params): array
    {   
        $url = $this->host.$index."/".$doc."/".$id;
        $_parmas = json_encode($params);
        $data = json_decode(Request::post($url, $_parmas),true);
        return $data;
    }
    
    /**
     * 更新文档
     *
     * @param string $index  索引名称
     * @param string $doc    文档名称
     * @param int $id        文档id
     * @param array $params  文档  ['fields_name'=>'data']
     * @return array
     */
    public function update_doc(string $index, string $doc, int $id, string $params): array
    {
        $url = $this->host.$index."/".$doc."/".$id."/_update";
        $data = json_decode(Request::post($url, json_encode(['doc'=>$params])),true);
        return $data;
    }

    /**
     * 删除文档
     *
     * @param string $index   索引名称
     * @param string $doc     文档类型
     * @param int $id         文档id
     * @return array
     */
    public function delete_doc(string $index, string $doc, int $id): array
    {
        $url = $this->host . $index . "/" . $doc . "/" . $id;
        $data = json_decode(Request::delete($url), true);
        return $data;
    }
    
    function __destruct()
    {}
}
