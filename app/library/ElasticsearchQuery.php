 <?php
/**
 * elasticsearch 查询
 * 
 * @author chen <809783120@qq.com> 2017-12-07
 *
 */
class ElasticsearchQuery
{
    private $instance = null;
    
    private $host = null;
    
    private $filter = array();
    
    private $bool = array();
    
    protected $body = array();
    
    public function __construct($config = array())
    {
        $this->instance = new Elasticsearch($config);
        $this->instance->setHost();
        $this->host = $this->instance->host;
    }
    /**
     * 设置索引的名称和文档类型，文档id
     * 
     * @param array $params  ['index=>'demo','doc'>'demo']
     * @return self
     */
    public function body(array $params):self
    {
        $this->body = $params;
        return $this;
    }
    
    /**
     * 查询单条文档 按照id查
     * 
     * @param int $id   文档id
     * @param string $field  查询的字段
     * @throws Exception
     * @return array
     */
    public function get(int $id, string $field = ''): array
    {
        try {
            if (empty($this->body)) {
                throw new Exception("索引名称和文档类型不能为空");
            }
            $url = $this->host.$this->body['index']."/".$this->body['doc']."/".$id;
            if (!empty($field)){
                $url.=$url."/_source_include=".$field;
            }
            $data = json_decode(Request::get($url),true);
        }catch (Exception $e){
            $data['message'] = $e->getMessage();
            $data['line'] = $e->getLine();
            $data['file'] = $e->getFile();
        }
        return $data;
    }
    
    /**
     * 获取文档内容
     * 
     * @param int $id   文档id
     * @param string $field  查询字段
     * @throws Exception
     * @return array
     */
    public function get_source(int $id, string $field = ''): array
    {
        try {
            if (empty($this->body)) {
                throw new Exception("索引名称和文档类型不能为空");
            }
            if (!empty($field)){
                $url=$this->host.$this->body['index']."/".$this->body['doc']."/".$id."?_source_include=".$field;
            }else {
                $url = $this->host.$this->body['index']."/".$this->body['doc']."/".$id."/_source";
            }
            $data = json_decode(Request::get($url),true);
        }catch (Exception $e){
            $data['message'] = $e->getMessage();
            $data['line'] = $e->getLine();
            $data['file'] = $e->getFile();
        }
        return $data;
    }

    /**
     * bool查询
     * 
     * @param array $bool
     * @return self
     */
    public function bool(array $bool): self
    {
        $this->bool = $bool;
        return $this;
    }
    
    /**
     * 过滤  用于bool查询内
     * 
     * @param array $filter
     * @return self
     */
    public function filter(array $filter): self
    {
        $this->filter = $filter;
        return $this;
    }
    
    /**
     * 查询
     * 
     * @param string $index
     * @param string $doc
     * @return array
     */
    public function query(string $index, string $doc = ''): array
    {
        $params['query'] = [
            'bool'=>[$this->bool],
            'filter'=>[$this->filter]
        ];
        $url = $this->host.$index."/_search";
        $result = json_decode(Request::post($url,json_encode($params)),true);
        return $result;
    }
    
    /**
     * 模糊匹配
     * 
     * @param array $field
     * @throws Exception
     * @return array
     */
    public function match(array $field): array
    {
        $params['query'] = [
            'match'=>$field
        ];
        if (empty($this->body)) {
            throw new Exception("索引名称和文档类型不能为空");
        }
        $doc = !empty($this->body['doc'])?"/".$this->body['doc']:'';
        $url = $this->host.$this->body['index'].$doc;
        return json_decode(Request::post($url,json_encode($params)),true);
    }
    
    /**
     * 短语匹配
     * 
     * @param array $field
     * @throws Exception
     * @return array
     */
    public function match_phrase(array $field): array
    {
        $params['query'] = [
            'match_phrase'=>$field
        ];
        if (empty($this->body)) {
            throw new Exception("索引名称和文档类型不能为空");
        }
        $doc = !empty($this->body['doc'])?"/".$this->body['doc']:'';
        $url = $this->host.$this->body['index'].$doc;
        return json_decode(Request::get($url,json_encode($params)),true);
    }
    
    /**
     * 多文档查询
     * 
     * @param array $params   查询的索引和文档 [['index'=>'index_name','type'=>'doc_name','id'=>'1']]
     * @throws Exception
     * @return array
     */
    public function mget(array $params): array
    {
        if (empty($params)){
            throw new Exception("索引名称和文档类型不能为空");
        }
        try {
            $url = $this->host."/_mget";
            $_params['docs'] = [];
            foreach ($params as $vo) {
                $_params['docs'][]=[
                  '_index'=>$vo['index'],
                  '_type'=>$vo['type'],
                  '_id'=>$vo['id']
                ];
            }
            var_dump($_params);
            die();
            return json_decode(Request::post($url,json_encode($_params)),true);
        }catch (Exception $e) {
            $data['message'] = $e->getMessage();
            $data['line'] = $e->getLine();
            $data['file'] = $e->getFile();
            return $data;
        }
    }
    
    public function mget_index(string $index, string $doc_name, array $id): array
    {
        try {
            $url = $this->host.$index."/".$doc_name."/_mget";
            
        }catch (Exception $e) {
            
        }
    }

    function __destruct()
    {}
}

