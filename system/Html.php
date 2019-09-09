<?php

namespace System;

class Html
{
    private $html = [];
    
    public function __call($method, $params)
    {
        if(strpos($method, 'set') === 0)
        {
            $key = strtolower(str_replace('set', '', $method));
            $this->html[$key] = $params[0];
        }
        elseif(strpos($method, 'get') === 0)
        {
            if(count($params) > 0 && ! is_string($params[0]))
            {
                throw new Exception("$method() expect to be string");
            }
            
            $key = strtolower(str_replace('get', '', $method));
            return $this->html[$key];
        }
        else
        {
            throw new Exception("Method $method doesn't exists");
        }
    }
    
}