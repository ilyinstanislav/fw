<?php

abstract class Model
{
    protected $_data = null;
    public $table = '';
    public $primary = 'id';

    function __construct()
    {
        $this->_data = new stdClass();
    }

    public function save($values)
    {
        if (!$this->__get($this->primary)) {
            $result = App::getInstance()->db->query('insert into ' . $this->table . ' set ' . $this->serialize($values));
            $this->__set($this->primary, App::getInstance()->db->id());
            return $result;
        } else {
            return App::getInstance()->db->query(
                'update ' . $this->table .
                ' set ' . $this->serialize($values) .
                ' where ' . $this->primary . '=' . $this->__get($this->primary));
        }
    }

    public static function getAll()
    {
        $modelName = get_called_class();
        $model = new $modelName;

        $items = App::getInstance()->db->query("select * from {$model->table}")->rows();
        $results = [];
        foreach ($items as $item) {
            $model = new $modelName;
            $model->__attributes = $item;
            $results[] = $model;
        }

        return $results;
    }

    public static function findOne($id)
    {
        $id = intval($id);
        $modelName = get_called_class();
        $model = new $modelName;

        $item = App::getInstance()->db->query('select * from ' . $model->table .
            ' where ' . $model->primary . '=' . $id)->row();

        if (!$item) {
            return null;
        }

        $model->__attributes = $item;
        return $model;
    }

    protected function serialize($array)
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (!is_numeric($value)) {
                $result[] = "`$key`='$value'";
            } else {
                $result[] = "`$key`=$value";
            }
        }

        return implode(',', $result);
    }

    public function __set($name, $value)
    {
        if ($name === '__attributes') {
            foreach ($value as $key => $val) {
                $this->__set($key, $val);
            }
            return;
        }
        if (method_exists($this, 'set' . $name)) {
            return call_user_func(array($this, 'set' . $name), $value);
        }

        $this->_data->$name = $value;
    }

    public function __get($name)
    {
        if ($name === '__attributes') {
            return $this->_data;
        }
        if (method_exists($this, 'get' . $name)) {
            return call_user_func(array($this, 'get' . $name));
        }
        return property_exists($this->_data, $name) ? $this->_data->$name : null;
    }
}