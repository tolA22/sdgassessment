<?php

use Src\Controller\LogController;

// $data = array("region"=>array("name"=>"Africa","avgAge"=>19.7,"avgDailyIncomeInUSD"=>5,"avgDailyIncomePopulation"=>0.71),"periodType"=>"days","timeToElapse"=>58,"reportedCases"=>674,"population"=>66622705,"totalHospitalBeds"=>1380614 ) ;
// $data =  json_encode($data);
// echo $data;

// print_r( covid19ImpactEstimator($data) );

function covid19ImpactEstimator($data)
{

  $output= array("data"=>$data);
  $output = currentlyInfected($output);
  $output = currentlyInfectedAfterXdays($output);
  $output = severeCasesByRequestedTime($output);
  $output = hospitalBedsByRequestedTime($output);
  $output = casesForICUByRequestedTime($output);
  $output = casesForVentilatorsByRequestedTime($output);
  $output = dollarsInFlight($output);
  return $output;
}

function getDays($type,$tTE){

  if(strtolower($type) == "days"){
    return $tTE;
  }else if(strtolower($type)== "weeks"){
    return $tTE * 7;
  }else if(strtolower($type) == "months"){
    return $tTE * 30;
  }else{
    //throw an exception here
  }

}

function impactCurrentlyInfected($reportedCases){
  return $reportedCases*10;
}

function severeImpactCurrentlyInfected($reportedCases){
  return $reportedCases*50;
}

function currentlyInfected($data){

  $reportedCases = $data["data"]["reportedCases"];
  $data["impact"]["currentlyInfected"] = (int)impactCurrentlyInfected($reportedCases);
  $data["severeImpact"]["currentlyInfected"] = (int)severeImpactCurrentlyInfected($reportedCases);
  return $data;
}


function impactCurrentlyInfectedAfterXdays($currentlyInfected,$power){
  return $currentlyInfected*(2**$power);
}


function currentlyInfectedAfterXdays($data){
  $power = (int)(getDays($data["data"]["periodType"],$data["data"]["timeToElapse"]) / 3);
  $data["impact"]["infectionsByRequestedTime"] = (int)impactCurrentlyInfectedAfterXdays($data["impact"]["currentlyInfected"],$power);
  $data["severeImpact"]["infectionsByRequestedTime"] = (int)impactCurrentlyInfectedAfterXdays($data["severeImpact"]["currentlyInfected"],$power);
  return $data;
}

function severeCasesByRequestedTime($data){
  $data["impact"]["severeCasesByRequestedTime"] = (int)impactSevereCasesByRequestedTime($data["impact"]["infectionsByRequestedTime"]);
  $data["severeImpact"]["severeCasesByRequestedTime"] =  (int)impactSevereCasesByRequestedTime($data["severeImpact"]["infectionsByRequestedTime"]); 
  return $data;
}

function impactSevereCasesByRequestedTime($infectionsByRequestedTime){
  return 0.15 * $infectionsByRequestedTime;
}


function hospitalBedsByRequestedTime($data){
  $totalHospitalBeds = $data["data"]["totalHospitalBeds"];
  $data["impact"]["hospitalBedsByRequestedTime"] = (int)impactHospitalBedsByRequestedTime($totalHospitalBeds,$data["impact"]["severeCasesByRequestedTime"]);
  $data["severeImpact"]["hospitalBedsByRequestedTime"] =  (int)impactHospitalBedsByRequestedTime($totalHospitalBeds,$data["severeImpact"]["severeCasesByRequestedTime"]); 
  return $data;
}

function impactHospitalBedsByRequestedTime($totalHospitalBeds, $severeCasesByRequestedTime){
  return (0.35 * $totalHospitalBeds) - $severeCasesByRequestedTime;
}


function casesForICUByRequestedTime($data){
  $data["impact"]["casesForICUByRequestedTime"] = (int)impactCasesForICUByRequestedTime($data["impact"]["infectionsByRequestedTime"]);
  $data["severeImpact"]["casesForICUByRequestedTime"] =  (int)impactCasesForICUByRequestedTime($data["severeImpact"]["infectionsByRequestedTime"]); 
  return $data;
}

function impactCasesForICUByRequestedTime($infectionsByRequestedTime){
  return 0.05 * $infectionsByRequestedTime;
}

function casesForVentilatorsByRequestedTime($data){
  $data["impact"]["casesForVentilatorsByRequestedTime"] = (int)impactCasesForVentilatorsByRequestedTime($data["impact"]["infectionsByRequestedTime"]);
  $data["severeImpact"]["casesForVentilatorsByRequestedTime"] =  (int)impactCasesForVentilatorsByRequestedTime($data["severeImpact"]["infectionsByRequestedTime"]); 
  return $data;
}

function impactCasesForVentilatorsByRequestedTime($infectionsByRequestedTime){
  return 0.02 * $infectionsByRequestedTime;
}

function dollarsInFlight($data){
  $amount=  $data["data"]["region"]["avgDailyIncomeInUSD"];
  $rate = $data["data"]["region"]["avgDailyIncomePopulation"];
  $period = getDays($data["data"]["periodType"],$data["data"]["timeToElapse"]);
  $data["impact"]["dollarsInFlight"] = (int)impactDollarsInFlight($data["impact"]["infectionsByRequestedTime"],$rate,$amount,$period);
  $data["severeImpact"]["dollarsInFlight"] =  (int)impactDollarsInFlight($data["severeImpact"]["infectionsByRequestedTime"],$rate,$amount,$period); 
  return $data;
}

function impactDollarsInFlight($infectionsByRequestedTime,$rate,$amount,$period){
  return ($infectionsByRequestedTime * $rate *$amount) / $period;
}

function convertToXML($data){
  return arrayToXml($data);
  
}

function arrayToXml($array, $rootElement = null, $xml = null) { 
  $_xml = $xml; 
    
  // If there is no Root Element then insert root 
  if ($_xml === null) { 
      $_xml = new SimpleXMLElement($rootElement !== null ? $rootElement : '<root/>'); 
  } 
    
  // Visit all key value pair 
  foreach ($array as $k => $v) { 
        
      // If there is nested array then 
      if (is_array($v)) {  
            
          // Call function for nested array 
          arrayToXml($v, $k, $_xml->addChild($k)); 
          } 
            
      else { 
            
          // Simply add child element.  
          $_xml->addChild($k, $v); 
      } 
  } 
    
  return $_xml->asXML(); 
} 

function logs($dbconnection,$data){
  $logController = new LogController($dbconnection);
  return $logController->insert($data);
}

function convertToText($data){
  $str = "";
  foreach($data as $datum){
    foreach($datum as $key=>$value){
      $str.= $value ;
      if($key == "time"){
        $str.= " ms";
      }
      if($key == "verb" && $value == "GET"){
        $str.="\t\t\t";
      }else{
        $str.="\t\t";
      }
       
    }
    $str.="\n";
  }
  return $str;
}