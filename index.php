<?php

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
    // print_r($request->getBody());
    $estimatorController  = new EstimatorController();
    $response = json_encode($estimatorController->covidEstimator($request->getBody()));
    header('Content-Type: application/json');
    // print($response);

    return $response;

  });

  $router->post('/api/v1/on-covid-19/json', function($request) {
    // print_r($request->getBody());

    $estimatorController  = new EstimatorController();
    $response = json_encode($estimatorController->covidEstimator($request->getBody()));
    header('Content-Type: application/json');
    // print($response);

    return $response;
  });

  $router->post('/api/v1/on-covid-19/xml', function($request) {
    // print_r($request->getBody());

    $estimatorController  = new EstimatorController();
    $data = $estimatorController->covidEstimator($request->getBody());
    $response = convertToXML($data);
    header('Content-Type: application/xml');
    // print($response);
    return $response;
  });

  $router->get('/api/v1/on-covid-19/logs', function($request) use ($dbConnection) {

    $logController  = new LogController($dbConnection);
    $data = $logController->getLogs();
    // print_r($data);
    $response = convertToText($data);
    header('Content-Type: text/plain');
    return $response;
  });

// echo "here";
