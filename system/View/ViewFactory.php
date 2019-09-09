<?php

namespace System\View;
use System\Exception;

class ViewFactory implements ViewInterface
{
    private $viewPath;

    private $data = [];

    private $output;

    public function __construct($viewPath, $data = [])
    {
        $this->preparePath($viewPath);
        $this->data = $data;
    }

    private function preparePath($viewPath)
    {
        $this->viewPath = view_path($viewPath);

        if(! file_exists($this->viewPath))
            throw new Exception("View [$viewPath] not found.");
    }

    public function getOutput()
    {
        if (is_array($this->data)) {
            extract($this->data, EXTR_PREFIX_SAME, "data");
        }

        if(is_null($this->output))
        {
            ob_start();    
            require $this->viewPath;
            $this->output = ob_get_clean();
        }

        return $this->output;
    }

    public function __toString()
    {
        return $this->getOutput();
    }

    public function __get($key)
    {
        $this->app->get($key);
    }
}