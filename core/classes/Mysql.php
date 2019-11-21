<?php

class Mysql
{
    private $connect_id = null;
    private $resource_id = null;
    private $sql = null;
    private $pfx = '';
    public $debug = true;

    function connect($config)
    {
        $this->connect_id = mysqli_connect($config['host'], $config['user'], $config['password'], $config['dbname']);
        if (!$this->connect_id) {
            $this->error();
            return false;
        }

        if (mysqli_select_db($this->connect_id, $config['dbname'])) {
            if (!isset($config['charset'])) {
                $config['charset'] = 'utf8';
            }
            $this->query("SET NAMES '" . $config['charset'] . "'")->
            query("SET CHARSET '" . $config['charset'] . "'")->
            query("SET CHARACTER SET '" . $config['charset'] . "'")->
            query("SET SESSION collation_connection = '" . $config['charset'] . "_general_ci'");
        }

        if (isset($config['dbprefix'])) {
            $this->pfx = $config['dbprefix'];
        }
    }

    function id()
    {
        return mysqli_insert_id($this->connect_id);
    }

    function beginTransaction()
    {
        return mysqli_begin_transaction($this->connect_id);
    }

    function commit()
    {
        return mysqli_commit($this->connect_id);
    }

    function rollBack()
    {
        return mysqli_rollback($this->connect_id);
    }

    function query($sql)
    {
        $this->sql = preg_replace('@#__@u', $this->pfx, $sql);
        $this->resource_id = mysqli_query($this->connect_id, $this->sql);
        if ($this->debug and !$this->resource_id) {
            $this->error();
        }

        return $this;
    }

    public function exec()
    {
        if (gettype($this->resource_id) == 'boolean') {
            return $this->resource_id;
        }
        return false;
    }

    function row($field = false)
    {
        if ($this->resource_id and $row = mysqli_fetch_object($this->resource_id)) {
            return $field ? $row[$field] : $row;
        }
        return null;
    }

    function rows($field = false, $key = false)
    {
        $rows = array();
        while ($row = $this->row($field)) {
            if (!$key) {
                $rows[] = $row;
            } else {
                $rows[$row[$key]] = $row;
            }
        }
        return $rows;
    }

    function numRows()
    {
        return mysqli_affected_rows($this->connect_id);
    }

    public function error()
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => mysqli_error($this->connect_id)
        ]);
        die();
    }
}