<?php
echo "here";
require "bootstrap.php";


use Src\Services\Router;
use Src\Services\Request;

use Src\Controller\EstimatorController;
use Src\Controller\LogController;


$router = new Router(new Request,$dbConnection);


// $router->get('/', function($request) {
//     return "welcome";
// });


$router->post('/api/v1/on-covid-19/', function($request) {
    $estimatorController  = new EstimatorController();
    $response = json_encode($estimatorController->covidEstimator($request->getBody()));
    return $response;

  });

  $router->post('/api/v1/on-covid-19/json', function($request) {
    $estimatorController  = new EstimatorController();
    $response = json_encode($estimatorController->covidEstimator($request->getBody()));
    return $response;
  });

  $router->post('/api/v1/on-covid-19/xml', function($request) {
    $estimatorController  = new EstimatorController();
    $data = $estimatorController->covidEstimator($request->getBody());
    $response = convertToXML($data);
    return $response;
  });

  $router->get('/api/v1/on-covid-19/logs', function($request) use ($dbConnection) {

    $logController  = new LogController($dbConnection);
    $data = $logController->getLogs();
    // print_r($data);
    $response = convertToText($data);
    return $response;
  });

// echo "here";
