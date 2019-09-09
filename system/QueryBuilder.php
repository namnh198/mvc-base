<?php

namespace System;
use \PDO;
use \PDOException;

class QueryBuilder
{
    private static $connection;

    private static $table;

    private static $bindings = [];

    private static $data = [];

    private static $selects = [];

    private static $wheres = [];

    private static $limit;

    private static $offset;

    private static $joins = [];

    private static $havings = [];

    private static $orderBy = [];

    private static $groupBy = [];

    private static $rows = 0;

    public function __construct()
    {
        if(! $this->isConnected()) 
        {
            $this->connect();
        }
    }

    private function isConnected()
    {
        return (static::$connection instanceof PDO);
    }

    public function connect()
    {
        extract(file_call('config/database.php'));

        try
        {
            static::$connection = new PDO("$dbconn:host=$dbhost;port=$dbport;dbname=$dbname", $dbuser, $dbpass);
            static::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            static::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            static::$connection->exec('SET NAMES utf8');
        }
        catch(PDOException $e)
        {
            throw new Exception($e->getMessage());
        }
    }

    public static function connection()
    {
        return static::$connection;
    }

    public static function table($table)
    {
        static::$table = $table;
        return new static;
    }

    public static function from($table)
    {
        return static::table($table);
    }

    public static function insert($table = null)
    {
        if($table)
        {
            static::table($table);
        }

        $sql = 'INSERT INTO ' . static::$table . ' SET '; 
        $sql .= static::setField();

        static::query($sql, static::$bindings);
        static::reset();

        return static::connection()->lastInsertId();
    }

    public static function update($table = null)
    {
        if($table) 
        {
            static::table($table);
        }

        $sql = 'UPDATE ' . static::$table . ' SET '; 
        $sql .= static::setField();

        if(static::$wheres) 
        {
            $sql .= ' WHERE ' . implode(' ', static::$wheres);
        }

        $query = static::query($sql, static::$bindings);
        static::reset();

        return $query->rowCount();
    }

    public static function fetch($table = null)
    {
        if($table) 
        {
            static::table($table);
        }

        $sql = static::fetchStatment();

        $result = static::query($sql, static::$bindings)->fetch();

        static::reset();

        return $result;
    }

    public static function fetchAll($table = null)
    {
        if($table)
        {
            static::table($table);
        }

        $sql = static::fetchStatment();

        $query = static::query($sql, static::$bindings);
        
        $results = $query->fetchAll();
        static::$rows = $query->rowCount();

        static::reset();

        return $results;
    }

    public static function delete($table = null)
    {
        if($table) 
        {
            static::table($table);
        }

        $sql = 'DELETE FROM ' . static::$table;

        if(static::$wheres) 
        {
            $sql .= ' WHERE ' . implode(' ', static::$wheres);
        }

        $query = static::query($sql, static::$bindings);
        static::reset();

        return $query->rowCount();
    }

    public static function rows()
    {
        return static::$rows;
    }

    public static function data($key, $value = null)
    {
        if(is_array($key))
        {
            static::$data = array_merge(static::$data, $key);
            static::addToBinding($key);
        }
        else 
        {
            static::$data[$key] = $value;
            static::addToBinding($value);
        }

        return new static;
    }

    public static function where()
    {
        $bindings = func_get_args();
        $sql = array_shift($bindings);
        static::addToBinding($bindings);
        static::$wheres[] = $sql;
        return (new static);
    }

    public static function select(...$selects)
    {
        $selects = func_get_args();

        static::$selects = array_merge(static::$selects, $selects);

        return (new static);
    }

    public static function join($join)
    {
        static::$joins[] = $join;
        return (new static);
    }

    public static function limit($limit, $offset = 0)
    {
        static::$limit  = $limit;
        static::$offset = $offset;
        return (new static);
    }

    public static function orderBy($column, $sort = 'ASC')
    {
        static::$orderBy = [$column, $sort];
        return (new static);
    }

    public static function having()
    {
        $bindings = func_get_args();
        $sql = array_shift($bindings);
        static::addToBinding($bindings);
        static::$havings[] = $sql;
        return (new static);
    }

    public static function groupBy(...$arguments)
    {
        static::$groupBy = $arguments;
        return (new static);
    }

    public static function query()
    {
        $bindings = func_get_args();
        $sql = array_shift($bindings);

        if(count($bindings) === 1 && is_array($bindings[0])) 
        {
            $bindings = $bindings[0];
        }

        try
        {
            $query = static::connection()->prepare($sql);

            foreach ($bindings as $key => $value) 
                $query->bindValue($key+1, _e($value));
            
            $query->execute();

            return $query;
        }
        catch(PDOException $e)
        {
            throw new Exception($e->getMessage());
        }
    }

    private static function setField()
    {
        $sql = '';

        foreach (array_keys(static::$data) as $key) 
        {
            $sql .= "`$key` = ? , ";
        }
        
        return rtrim($sql, ', ');
    }

    private static function fetchStatment()
    {
        $sql = 'SELECT ';

        if(static::$selects) 
        {
            $sql .= implode(' , ' , static::$selects);
        }
        else 
        {
            $sql .= ' * ';
        }

        $sql .= ' FROM ' . static::$table . ' ';

        if(static::$joins) 
        {
            $sql .= implode(' ', static::$joins);
        }

        if(static::$wheres) 
        {
            $sql .= ' WHERE ' . implode(' ', static::$wheres);
        }

        if(static::$havings) 
        {
            $sql .= ' WHERE ' . implode(' ', static::$havings);
        }

        if(static::$orderBy) 
        {
            $sql .= ' ORDER BY ' . implode(' ', static::$orderBy);
        }

        if(static::$limit) 
        {
            $sql .= ' LIMIT ' . static::$limit;
        }

        if(static::$offset) 
        {
            $sql .= ' OFFSET ' . static::$offset;
        }

        if(static::$groupBy) 
        {
            $sql .= ' GROUP BY ' . implode(' ', static::$groupBy);
        }

        return $sql;
    }

    private static function addToBinding($value)
    {
        if(is_array($value))
        {
            static::$bindings = array_merge(static::$bindings, array_values($value));
        }
        else
        {
            static::$bindings[] = $value;
        }
    }

    private static function reset()
    {
        static::$table    = null;
        static::$bindings = [];
        static::$data     = [];
        static::$selects  = [];
        static::$wheres   = [];
        static::$limit    = null;
        static::$offset   = null;
        static::$joins    = [];
        static::$havings  = [];
        static::$orderBy  = [];
        static::$groupBy  = [];
    }
}
