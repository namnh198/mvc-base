<?php

namespace System;
use System\QueryBuilder as DB;

abstract class Model
{
    protected $table;

    protected $app;

    public function __construct()
    {
        $app = App::getInstance();
        $this->app = $app;
    }

    public static function create($data, $table = null)
    {
        return DB::table((new static)->table)->data($data)->insert();;
    }

    public static function all($sort = 'DESC')
    {
        return DB::table((new static)->table)->orderBy('id', $sort)->fetchAll();
    }

    public static function get($id)
    {
        return DB::table((new static)->table)->where('id=?', $id)->fetch();
    }

    public static function exists($wheres)
    {
        return (bool) DB::table((new static)->table)->select($key)->where("$key=?", $value)->fetch();
    }

    public static function __callStatic($method, $args)
    {
        return call_user_func_array(['\\System\QueryBuilder', $method], $args);
    }

    public function __get($key)
    {
        return $this->app->get($key);
    }
}