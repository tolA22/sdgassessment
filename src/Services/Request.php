<?php

namespace Src\Services;

class Request implements IRequest
{
  function __construct()
  {
    $this->bootstrapSelf();
  }

  private function bootstrapSelf()
  {
    foreach($_SERVER as $key => $value)
    {
      $this->{$this->toCamelCase($key)} = $value;
    }
  }

  private function toCamelCase($string)
  {
    $result = strtolower($string);
        
    preg_match_all('/_[a-z]/', $result, $matches);

    foreach($matches[0] as $match)
    {
        $c = str_replace('_', '', strtoupper($match));
        $result = str_replace($match, $c, $result);
    }

    return $result;
  }

  public function getBody()
  {
    if($this->requestMethod === "GET")
    {
      return;
    }


    if ($this->requestMethod == "POST")
    {
    print($this->contentType);
    if($this->contentType == "application/json"){
        $body = json_decode(file_get_contents("php://input"),true);
        // print_r($body);
        return $body;
    }

      return $_POST;
    }
  }
}