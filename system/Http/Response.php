<?php

namespace System\Http;
use System\App;

class Response
{
    private $app;

    private $headers = [];

    private $content = '';

    public function __construct(App $app)
    {   
        $this->app = $app;
    }

    public function setHeaders($key, $value)
    {
        $this->headers[$key] = $value;   
    }

    public function setOutput($content)
    {
        $this->content = $content;
    }

    public function send()
    {
        $this->sendHeaders();
        $this->sendOutput();
    }


    public function api($data, $code = 200)
    {
        $this->response->setHeaders('Content-Type', 'application/json');
        new Http\HttpResponseCode($code);
        return json_encode($data);
    }

    public function message($message, $code = 200)
    {
        $reponse['message'] = $message;
        $this->api($reponse, $code);
    }

    private function sendHeaders()
    {
        foreach($this->headers as $header => $value)
        {
            header("$header: $value");
        }
    }

    private function sendOutput()
    {
        echo $this->content;
    }
}