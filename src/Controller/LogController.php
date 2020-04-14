<?php

namespace Src\Controller;

use Src\Model\Log;


class LogController{

    private $model;
    function __construct($dbconnection){
        $this->model = new Log($dbconnection);
    }

    public function insert($data){
        return $this->model->insert($data);
    }

    public function getLogs(){
        return $this->model->findAll();
    }
}