<?php

class Payment_Request
{
    public $url;
    public $code;

    function __construct($code, $url)
    {
        $this->code = $code;
        $this->url = $url;
    }
}
