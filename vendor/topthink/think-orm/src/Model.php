<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think;

use ArrayAccess;
use JsonSerializable;
use think\Db;
use think\db\Query;

/**
 * Class Model
 * @package think
 * @mixin Query
 */
abstract class Model implements JsonSerializable, ArrayAccess
{
    use model\concern\Attribute;
    use model\concern\RelationShip;
    use model\concern\ModelEvent;
    use model\concern\TimeStamp;
    use model\concern\Conversion;

    /**
     * 数据是否存在
     * @var bool
     */
    private $exists = false;

    /**
     * 是否强制更新所有数据
     * @var bool
     */
    private $force = false;

    /**
     * 是否Replace
     * @var bool
     */
    private $replace = false;

    /**
     * 更新条件
     * @var array
     */
    private $updateWhere;

    /**
     * 数据库配置
     * @var string
     */
    protected $connection;

    /**
     * 数据库查询对象类名
     * @var string
     */
    protected $query;

    /**
     * 模型名称
     * @var string
     */
    protected $name;

    /**
     * 数据表名称
     * @var string
     */
    protected $table;

    /**
     * 写入自动完成定义
     * @var array
     */
    protected $auto = [];

    /**
     * 新增自动完成定义
     * @var array
     */
    protected $insert = [];

    /**
     * 更新自动完成定义
     * @var array
     */
    protected $update = [];

    /**
     * 初始化过的模型.
     * @var array
     */
    protected static $initialized = [];

    /**
     * 查询对象实例
     * @var Query
     */
    protected $queryInstance;

    /**
     * 软删除字段默认值
     * @var mixed
     */
    protected $defaultSoftDelete;

    /**
     * 全局查询范围
     * @var array
     */
    protected $globalScope = [];

    /**
     * 延迟保存信息
     * @var bool
     */
    private $lazySave = false;

    /**
     * 架构函数
     * @access public
     * @param  array $data 数据
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;

        if ($this->data) {
            // 废弃字段
            foreach ((array) $this->disuse as $key) {
                if (array_key_exists($key, $this->data)) {
                    unset($this->data[$key]);
                }
            }
        }

        // 记录原始数据
        $this->origin = $this->data;

        $config = Db::getConfig();

        if (empty($this->name)) {
            // 当前模型名
            $name       = str_replace('\\', '/', static::class);
            $this->name = basename($name);
            if (!empty($config['class_suffix'])) {
                $suffix     = basename(dirname($name));
                $this->name = substr($this->name, 0, -strlen($suffix));
            }
        }

        if (is_null($this->autoWriteTimestamp)) {
            // 自动写入时间戳
            $this->autoWriteTimestamp = $config['auto_timestamp'];
        }

        if (is_null($this->dateFormat)) {
            // 设置时间戳格式
            $this->dateFormat = $config['datetime_format'];
        }

        if (is_null($this->query)) {
            // 设置查询对象
            $this->query = $config['query'];
        }

        if (!empty($this->connection) && is_array($this->connection)) {
            // 设置模型的数据库连接
            $this->connection = array_merge($config, $this->connection);
        }

        // 执行初始化操作
        $this->initialize();
    }

    /**
     * 获取当前模型名称
     * @access public
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 创建新的模型实例
     * @access public
     * @param  array    $data 数据
     * @param  bool     $isUpdate 是否为更新
     * @param  mixed    $where 更新条件
     * @return Model
     */
    public function newInstance(array $data = [], bool $isUpdate = false, $where = null): Model
    {
        $model = (new static($data))->isUpdate($isUpdate, $where);

        $model->trigger('after_read');
        return $model;
    }

    /**
     * 创建模型的查询对象
     * @access protected
     * @return Query
     */
    protected function buildQuery(): Query
    {
        $query = Db::buildQuery($this->query, $this->connection);

        $query->model($this)
            ->name($this->name)
            ->json($this->json, $this->jsonAssoc)
            ->setFieldType($this->schema);

        if (!empty($this->table)) {
            $query->table($this->table);
        }

        return $query->pk($this->pk);
    }

    /**
     * 获取当前模型的数据库查询对象
     * @access public
     * @param  Query $query 查询对象实例
     * @return $this
     */
    public function setQuery(Query $query)
    {
        $this->queryInstance = $query;
        return $this;
    }

    /**
     * 获取当前模型的数据库查询对象
     * @access public
     * @param  array|false $scope 使用的全局查询范围
     * @return Query
     */
    public function db($scope = []): Query
    {
        if ($this->queryInstance) {
            return $this->queryInstance;
        }

        $query = $this->buildQuery();

        // 软删除
        if (property_exists($this, 'withTrashed') && !$this->withTrashed) {
            $this->withNoTrashed($query);
        }

        // 全局作用域
        $globalScope = is_array($scope) && $scope ? $scope : $this->globalScope;

        if ($globalScope && false !== $scope) {
            $query->scope($globalScope);
        }

        // 返回当前模型的数据库查询对象
        return $query;
    }

    /**
     *  初始化模型
     * @access private
     * @return void
     */
    private function initialize(): void
    {
        if (!isset(static::$initialized[static::class])) {
            if ($this->observerClass) {
                // 注册模型观察者
                static::observe($this->observerClass);
            }
            static::$initialized[static::class] = true;
            static::init();
        }
    }

    /**
     * 初始化处理
     * @access protected
     * @return void
     */
    protected static function init(): void
    {}

    /**
     * 数据自动完成
     * @access protected
     * @param  array $auto 要自动更新的字段列表
     * @return void
     */
    protected function autoCompleteData(array $auto = []): void
    {
        foreach ($auto as $field => $value) {
            if (is_integer($field)) {
                $field = $value;
                $value = null;
            }

            if (!isset($this->data[$field])) {
                $default = null;
            } else {
                $default = $this->data[$field];
            }

            $this->setAttr($field, !is_null($value) ? $value : $default);
        }

    }

    protected function checkData(): void
    {}

    protected function checkResult($result): void
    {}

    /**
     * 更新是否强制写入数据 而不做比较
     * @access public
     * @param  bool $force
     * @return $this
     */
    public function force(bool $force = true)
    {
        $this->force = $force;
        return $this;
    }

    /**
     * 判断force
     * @access public
     * @return bool
     */
    public function isForce(): bool
    {
        return $this->force;
    }

    /**
     * 新增数据是否使用Replace
     * @access public
     * @param  bool $replace
     * @return $this
     */
    public function replace(bool $replace = true)
    {
        $this->replace = $replace;
        return $this;
    }

    /**
     * 设置数据是否存在
     * @access public
     * @param  bool $exists
     * @return $this
     */
    public function exists(bool $exists = true)
    {
        $this->exists = $exists;
        return $this;
    }

    /**
     * 判断数据是否存在数据库
     * @access public
     * @return bool
     */
    public function isExists(): bool
    {
        return $this->exists;
    }

    /**
     * 刷新模型数据
     * @access public
     * @param  bool $relation 是否刷新关联数据
     * @return $this
     */
    public function reflesh(bool $relation = false)
    {
        if ($this->exists) {
            $this->data   = $this->fetchArray()->find($this->getKey());
            $this->origin = $this->data;

            if ($relation) {
                $this->relation = [];
            }
        }

        return $this;
    }

    /**
     * 判断模型是否为空
     * @access public
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * 延迟保存当前数据对象
     * @access public
     * @param  array|bool  $data     数据
     * @return void
     */
    public function lazySave($data = [])
    {
        if (false === $data) {
            $this->lazySave = false;
        } else {
            if (is_array($data)) {
                $this->setAttrs($data);
            }

            $this->lazySave = true;
        }
    }

    /**
     * 保存当前数据对象
     * @access public
     * @param  array  $data     数据
     * @param  string $sequence 自增序列名
     * @return bool
     */
    public function save(array $data = [], string $sequence = null): bool
    {
        // 数据对象赋值
        $this->setAttrs($data);

        if ($this->isEmpty() || false === $this->trigger('before_write')) {
            return false;
        }

        $result = $this->exists ? $this->updateData() : $this->insertData($sequence);

        if (false === $result) {
            return false;
        }

        // 写入回调
        $this->trigger('after_write');

        // 重新记录原始数据
        $this->origin   = $this->data;
        $this->set      = [];
        $this->lazySave = false;

        return true;
    }

    /**
     * 检查数据是否允许写入
     * @access protected
     * @param  array   $append 自动完成的字段列表
     * @return array
     */
    protected function checkAllowFields(array $append = []): array
    {
        // 检测字段
        if (empty($this->field)) {
            if ($this->schema) {
                $this->field = array_keys($this->schema);
            } else {
                $query = $this->db();
                $table = $this->table ?: $query->getTable();

                $this->field = $query->getConnection()->getTableFields($table);
            }

            return $this->field;
        }

        $field = array_merge($this->field, $append);

        if ($this->autoWriteTimestamp) {
            array_push($field, $this->createTime, $this->updateTime);
        }

        if ($this->disuse) {
            // 废弃字段
            $field = array_diff($field, $this->disuse);
        }

        return $field;
    }

    /**
     * 保存写入数据
     * @access protected
     * @return bool
     */
    protected function updateData(): bool
    {
        // 自动更新
        $auto = array_merge($this->auto, $this->update);

        $this->autoCompleteData($auto);

        // 事件回调
        if (false === $this->trigger('before_update')) {
            return false;
        }

        $this->checkData();

        // 获取有更新的数据
        $data = $this->getChangedData();

        if (empty($data)) {
            // 关联更新
            if (!empty($this->relationWrite)) {
                $this->autoRelationUpdate();
            }

            return false;
        }

        if ($this->autoWriteTimestamp && $this->updateTime && !isset($data[$this->updateTime])) {
            // 自动写入更新时间
            $data[$this->updateTime] = $this->autoWriteTimestamp($this->updateTime);

            $this->data[$this->updateTime] = $data[$this->updateTime];
        }

        // 检查允许字段
        $allowFields = $this->checkAllowFields($auto);

        $where = $this->getUpdateWhere($data);

        foreach ((array) $this->relationWrite as $name => $val) {
            if (!is_array($val)) {
                continue;
            }

            foreach ($val as $key) {
                if (isset($data[$key])) {
                    unset($data[$key]);
                }
            }
        }

        // 模型更新
        $db = $this->db();
        $db->startTrans();

        try {
            $result = $db->where($where)
                ->strict(false)
                ->field($allowFields)
                ->update($data);

            $this->checkResult($result);

            // 关联更新
            if (!empty($this->relationWrite)) {
                $this->autoRelationUpdate();
            }

            $db->commit();

            // 更新回调
            $this->trigger('after_update');

            return true;
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    protected function getUpdateWhere(array &$data): array
    {
        // 保留主键数据
        foreach ($this->data as $key => $val) {
            if ($this->isPk($key)) {
                $data[$key] = $val;
            }
        }

        $pk    = $this->getPk();
        $array = [];

        foreach ((array) $pk as $key) {
            if (isset($data[$key])) {
                $array[] = [$key, '=', $data[$key]];
                unset($data[$key]);
            }
        }

        if (!empty($array)) {
            $where = $array;
        } else {
            $where = $this->updateWhere;
        }

        return $where;
    }

    /**
     * 新增写入数据
     * @access protected
     * @param  string   $sequence 自增名
     * @return bool
     */
    protected function insertData(string $sequence = null): bool
    {
        // 自动写入
        $auto = array_merge($this->auto, $this->insert);

        $this->autoCompleteData($auto);

        // 时间戳自动写入
        if ($this->autoWriteTimestamp) {
            if ($this->createTime && !isset($this->data[$this->createTime])) {
                $this->data[$this->createTime] = $this->autoWriteTimestamp($this->createTime);
            }

            if ($this->updateTime && !isset($this->data[$this->updateTime])) {
                $this->data[$this->updateTime] = $this->autoWriteTimestamp($this->updateTime);
            }
        }

        if (false === $this->trigger('before_insert')) {
            return false;
        }

        $this->checkData();

        // 检查允许字段
        $allowFields = $this->checkAllowFields($auto);

        $db = $this->db();
        $db->startTrans();

        try {
            $result = $db->strict(false)
                ->field($allowFields)
                ->insert($this->data, $this->replace, false, $sequence);

            // 获取自动增长主键
            if ($result && $insertId = $db->getLastInsID($sequence)) {
                $pk = $this->getPk();

                foreach ((array) $pk as $key) {
                    if (!isset($this->data[$key]) || '' == $this->data[$key]) {
                        $this->data[$key] = $insertId;
                    }
                }
            }

            // 关联写入
            if (!empty($this->relationWrite)) {
                $this->autoRelationInsert();
            }

            $db->commit();

            // 标记数据已经存在
            $this->exists = true;

            // 新增回调
            $this->trigger('after_insert');

            return true;
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 获取当前的更新条件
     * @access protected
     * @return mixed
     */
    protected function getWhere()
    {
        // 删除条件
        $pk = $this->getPk();

        if (is_string($pk) && isset($this->data[$pk])) {
            $where[] = [$pk, '=', $this->data[$pk]];
        } elseif (!empty($this->updateWhere)) {
            $where = $this->updateWhere;
        } else {
            $where = null;
        }

        return $where;
    }

    /**
     * 保存多个数据到当前数据对象
     * @access public
     * @param  array   $dataSet 数据
     * @param  boolean $replace 是否自动识别更新和写入
     * @return Collection
     * @throws \Exception
     */
    public function saveAll(array $dataSet, bool $replace = true): Collection
    {
        $db = $this->db();
        $db->startTrans();

        try {
            $pk = $this->getPk();

            if (is_string($pk) && $replace) {
                $auto = true;
            }

            $result = [];

            foreach ($dataSet as $key => $data) {
                if ($this->exists || (!empty($auto) && isset($data[$pk]))) {
                    $result[$key] = self::update($data, $this->field);
                } else {
                    $result[$key] = self::create($data, $this->field, $this->replace);
                }
            }

            $db->commit();

            return $this->toCollection($result);
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 是否为更新数据
     * @access public
     * @param  bool   $update
     * @param  mixed  $where
     * @return $this
     */
    public function isUpdate(bool $update = true, $where = null)
    {
        $this->exists = $update;

        if (!empty($where)) {
            $this->updateWhere = $where;
        }

        return $this;
    }

    /**
     * 删除当前的记录
     * @access public
     * @return bool
     */
    public function delete(): bool
    {
        if (!$this->exists || $this->isEmpty() || false === $this->trigger('before_delete')) {
            return false;
        }

        // 读取更新条件
        $where = $this->getWhere();

        $db = $this->db();
        $db->startTrans();

        try {
            // 删除当前模型数据
            $result = $db->where($where)->delete();

            // 关联删除
            if (!empty($this->relationWrite)) {
                $this->autoRelationDelete();
            }

            $db->commit();

            $this->trigger('after_delete');

            $this->exists   = false;
            $this->lazySave = false;

            return true;
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 设置自动完成的字段（ 规则通过修改器定义）
     * @access public
     * @param  array $fields 需要自动完成的字段
     * @return $this
     */
    public function auto(array $fields)
    {
        $this->auto = $fields;

        return $this;
    }

    /**
     * 写入数据
     * @access public
     * @param  array      $data  数据数组
     * @param  array      $field 允许字段
     * @param  bool       $replace 使用Replace
     * @return static
     */
    public static function create(array $data, array $allowField = [], bool $replace = false): Model
    {
        $model = new static();

        if (!empty($allowField)) {
            $model->allowField($allowField);
        }

        $model->isUpdate(false)->replace($replace)->save($data);

        return $model;
    }

    /**
     * 更新数据
     * @access public
     * @param  array      $data  数据数组
     * @param  array      $allowField 允许字段
     * @return static
     */
    public static function update(array $data, array $allowField = [])
    {
        $model = new static();

        if (!empty($allowField)) {
            $model->allowField($allowField);
        }

        $model->isUpdate(true)->save($data);

        return $model;
    }

    /**
     * 删除记录
     * @access public
     * @param  mixed $data 主键列表 支持闭包查询条件
     * @param  bool  $force 是否强制删除
     * @return bool
     */
    public static function destroy($data, bool $force = false): bool
    {
        if (empty($data) && 0 !== $data) {
            return false;
        }

        $model = new static();

        $query = $model->db();

        if (is_array($data) && key($data) !== 0) {
            $query->where($data);
            $data = null;
        } elseif ($data instanceof \Closure) {
            $data($query);
            $data = null;
        }

        $resultSet = $query->select($data);

        foreach ($resultSet as $data) {
            $data->force($force)->delete();
        }

        return true;
    }

    /**
     * 解序列化后处理
     */
    public function __wakeup()
    {
        $this->initialize();
    }

    public function __debugInfo()
    {
        return [
            'data'     => $this->data,
            'relation' => $this->relation,
        ];
    }

    /**
     * 修改器 设置数据对象的值
     * @access public
     * @param  string $name  名称
     * @param  mixed  $value 值
     * @return void
     */
    public function __set(string $name, $value): void
    {
        $this->setAttr($name, $value);
    }

    /**
     * 获取器 获取数据对象的值
     * @access public
     * @param  string $name 名称
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->getAttr($name);
    }

    /**
     * 检测数据对象的值
     * @access public
     * @param  string $name 名称
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return !is_null($this->getAttr($name));
    }

    /**
     * 销毁数据对象的值
     * @access public
     * @param  string $name 名称
     * @return void
     */
    public function __unset(string $name): void
    {
        unset($this->data[$name], $this->relation[$name]);
    }

    // ArrayAccess
    public function offsetSet($name, $value)
    {
        $this->setAttr($name, $value);
    }

    public function offsetExists($name): bool
    {
        return $this->__isset($name);
    }

    public function offsetUnset($name)
    {
        $this->__unset($name);
    }

    public function offsetGet($name)
    {
        return $this->getAttr($name);
    }

    /**
     * 设置使用的全局查询范围
     * @access public
     * @param  array|false $scope 启用的全局查询范围
     * @return Query
     */
    public static function useGlobalScope($scope)
    {
        $model = new static();

        return $model->db($scope);
    }

    public function __call($method, $args)
    {
        if ('withattr' == strtolower($method)) {
            return call_user_func_array([$this, 'withAttribute'], $args);
        }

        return call_user_func_array([$this->db(), $method], $args);
    }

    public static function __callStatic($method, $args)
    {
        $model = new static();

        return call_user_func_array([$model->db(), $method], $args);
    }

    /**
     * 析构方法
     * @access public
     */
    public function __destruct()
    {
        if ($this->lazySave) {
            $this->save();
        }
    }
}
