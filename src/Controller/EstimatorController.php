<?php

namespace Src\Controller;


class EstimatorController{


    function __construct(){

    }

    public function covidEstimator($data){
        return (covid19ImpactEstimator($data));
    }
}