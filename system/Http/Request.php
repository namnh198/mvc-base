<?php

namespace System\Http;
use System\App;

class Request
{
    private $url;

    private $files = [];

    public function prepareUrl()
    {
        $script = str_replace('/public', '', dirname($this->server('SCRIPT_NAME')));
        $requestUri = $this->server('REQUEST_URI');

        if(strpos($requestUri, '?'))
        {
            list($requestUri, $queryString) = explode('?', $requestUri);
        }

        $this->url = rtrim(preg_replace('#^'.$script.'#', '', $requestUri), '/');

        if($this->url === '') $this->url = '/';
    }

    public function get($key, $default = null)
    {
        $value = array_get($_GET, $key, $default);
        $value = is_array($value) ? array_filter($value) : trim($value);
        return _e($value);
    }

    public function post($key, $default = null)
    {
        $value = array_get($_POST, $key, $default);
        $value = is_array($value) ? array_filter($value) : trim($value);
        return _e($value); 
    }

    public function setPost($key, $value)
    {
        $_POST[$key] = _e($value);
    }

    public function file($input)
    {
        if(isset($this->files[$input]))
        {
            return $this->files[$input];
        }

        $uploadedFile = new UploadFile($input);
        $this->files[$input] = $uploadedFile;
        return $this->files[$input];
    }

    public function server($key, $default = null)
    {
        return array_get($_SERVER, $key, $default);
    }

    public function url()
    {
        return $this->url;
    }

    public function method()
    {
        return $this->server('REQUEST_METHOD', 'GET');
    }

    public function referer()
    {
        return $this->server('HTTP_REFERER');
    }
}