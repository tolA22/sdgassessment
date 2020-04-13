<?php

namespace Src\Services;

class Router
{
  private $request;
  private $supportedHttpMethods = array(
    "GET",
    "POST"
  );
  private $start;
  private $finish;
  private $dbConnection;

  function __construct(IRequest $request,$dbConnection)
  {
   $this->request = $request;
   $this->dbConnection = $dbConnection;
  }

  function __call($name, $args)
  {
      $this->start =  microtime(TRUE);
    list($route, $method) = $args;

    if(!in_array(strtoupper($name), $this->supportedHttpMethods))
    {
      $this->invalidMethodHandler();
      $logData = array("verb"=>$this->request->requestMethod,"uri"=>$this->request->requestUri);
      $this->finish =  microtime(TRUE);
        $logData["status"] = 405;
        $logData["time"] = $this->finish - $this->start;
        logs($this->dbConnection,$logData);

    return;
    }

    $this->{strtolower($name)}[$this->formatRoute($route)] = $method;
  }

  /**
   * Removes trailing forward slashes from the right of the route.
   * @param route (string)
   */
  private function formatRoute($route)
  {
    $result = rtrim($route, '/');
    if ($result === '')
    {
      return '/';
    }
    return $result;
  }

  private function invalidMethodHandler()
  {
    header("{$this->request->serverProtocol} 405 Method Not Allowed");
  }

  private function defaultRequestHandler()
  {
    //   echo "not found";
    header("{$this->request->serverProtocol} 404 Not Found");
    // echo "not found";

  }

  /**
   * Resolves a route
   */
  function resolve()
  {
    $logData = array("verb"=>$this->request->requestMethod,"uri"=>$this->request->requestUri);

    if(property_exists($this,strtolower($this->request->requestMethod)) == false){
        $this->defaultRequestHandler();
        $this->finish =  microtime(TRUE);
        $logData["status"] = 404;
        $logData["time"] = $this->finish - $this->start;
        logs($this->dbConnection,$logData);

      return;
    }
    $methodDictionary = $this->{strtolower($this->request->requestMethod)};
    
    $formatedRoute = $this->formatRoute($this->request->requestUri);
    if(is_null($methodDictionary) || array_key_exists($formatedRoute,$methodDictionary)){
        $method = $methodDictionary[$formatedRoute];

    }else{
        $this->defaultRequestHandler();
        $this->finish =  microtime(TRUE);
        $logData["status"] = 404;
        $logData["time"] = $this->finish - $this->start;
        logs($this->dbConnection,$logData);

      return;
    }
    
    if(empty($method))
    {
      $this->defaultRequestHandler();
      $this->finish =  microtime(TRUE);
        $logData["status"] = 404;
        $logData["time"] = $this->finish - $this->start;
        logs($this->dbConnection,$logData);

      return ;
    }

    print_r(call_user_func_array($method, array($this->request)));
    $this->finish =  microtime(TRUE);
    $logData["status"] = 200;
    $logData["time"] = $this->finish - $this->start;
    logs($this->dbConnection,$logData);
    // print_r($logData);
  }

  function __destruct()
  {
    $this->resolve();
  }
}