<?php

// $data = array("region"=>array("name"=>"Africa","avgAge"=>19.7,"avgDailyIncomeInUSD"=>5,"avgDailyIncomePopulation"=>0.71),"periodType"=>"days","timeToElapse"=>58,"reportedCases"=>674,"population"=>66622705,"totalHospitalBeds"=>1380614 ) ;
// $data =  json_encode($data);
// echo $data;

// print_r( covid19ImpactEstimator($data) );

function covid19ImpactEstimator($data)
{
  // $data = json_encode($data);
  // $data = json_decode($data,true);
  $output= array("data"=>$data);
  // $output = json_encode($output);
  // echo $output;
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



// function currentlyInfected($data){
//   //impact currently infected
//   $data = impactCurrentlyInfected($data);
//   //severe impact currently infected
//   $data = severeImpactCurrentlyInfected($data);
//   return $data;

// }


function impactCurrentlyInfected($reportedCases){
  return $reportedCases*10;
}

function severeImpactCurrentlyInfected($reportedCases){
  return $reportedCases*50;
}

function currentlyInfected($data){
  // echo $data;
  // $data = json_decode($data,true);
  // print_r($data["data"]);
  $reportedCases = $data["data"]["reportedCases"];
  $data["impact"]["currentlyInfected"] = (int)impactCurrentlyInfected($reportedCases);
  $data["severeImpact"]["currentlyInfected"] = (int)severeImpactCurrentlyInfected($reportedCases);
  // $data = json_encode($data);
  return $data;
}


function impactCurrentlyInfectedAfterXdays($currentlyInfected,$power){
  return $currentlyInfected*(2**$power);
}


function currentlyInfectedAfterXdays($data){
  // echo $data;
  // $data = json_decode($data,true);
  $power = (int)(getDays($data["data"]["periodType"],$data["data"]["timeToElapse"]) / 3);
  $data["impact"]["infectionsByRequestedTime"] = (int)impactCurrentlyInfectedAfterXdays($data["impact"]["currentlyInfected"],$power);
  $data["severeImpact"]["infectionsByRequestedTime"] = (int)impactCurrentlyInfectedAfterXdays($data["severeImpact"]["currentlyInfected"],$power);
  // $data = json_encode($data);
  return $data;
}

function severeCasesByRequestedTime($data){
  // echo $data;
  // $data = json_decode($data,true);;
  $data["impact"]["severeCasesByRequestedTime"] = (int)impactSevereCasesByRequestedTime($data["impact"]["infectionsByRequestedTime"]);
  $data["severeImpact"]["severeCasesByRequestedTime"] =  (int)impactSevereCasesByRequestedTime($data["severeImpact"]["infectionsByRequestedTime"]); 
  // $data = json_encode($data);
  return $data;
}

function impactSevereCasesByRequestedTime($infectionsByRequestedTime){
  return 0.15 * $infectionsByRequestedTime;
}


function hospitalBedsByRequestedTime($data){
  // $data = json_decode($data,true);
  $totalHospitalBeds = $data["data"]["totalHospitalBeds"];
  $data["impact"]["hospitalBedsByRequestedTime"] = (int)impactHospitalBedsByRequestedTime($totalHospitalBeds,$data["impact"]["severeCasesByRequestedTime"]);
  $data["severeImpact"]["hospitalBedsByRequestedTime"] =  (int)impactHospitalBedsByRequestedTime($totalHospitalBeds,$data["severeImpact"]["severeCasesByRequestedTime"]); 
  // $data = json_encode($data);
  return $data;
}

function impactHospitalBedsByRequestedTime($totalHospitalBeds, $severeCasesByRequestedTime){
  return (0.35 * $totalHospitalBeds) - $severeCasesByRequestedTime;
}

function casesForICUByRequestedTime($data){
  // $data = json_decode($data,true);
  $data["impact"]["casesForICUByRequestedTime"] = (int)impactCasesForICUByRequestedTime($data["impact"]["infectionsByRequestedTime"]);
  $data["severeImpact"]["casesForICUByRequestedTime"] =  (int)impactCasesForICUByRequestedTime($data["severeImpact"]["infectionsByRequestedTime"]); 
  // $data = json_encode($data);
  return $data;
}

function impactCasesForICUByRequestedTime($infectionsByRequestedTime){
  return 0.05 * $infectionsByRequestedTime;
}

function casesForVentilatorsByRequestedTime($data){
  // $data = json_decode($data,true);
  $data["impact"]["casesForVentilatorsByRequestedTime"] = (int)impactCasesForVentilatorsByRequestedTime($data["impact"]["infectionsByRequestedTime"]);
  $data["severeImpact"]["casesForVentilatorsByRequestedTime"] =  (int)impactCasesForVentilatorsByRequestedTime($data["severeImpact"]["infectionsByRequestedTime"]); 
  // $data = json_encode($data);
  return $data;
}

function impactCasesForVentilatorsByRequestedTime($infectionsByRequestedTime){
  return 0.02 * $infectionsByRequestedTime;
}

function dollarsInFlight($data){
  // $data = json_decode($data,true);
  $amount=  $data["data"]["region"]["avgDailyIncomeInUSD"];
  $rate = $data["data"]["region"]["avgDailyIncomePopulation"];
  $period = getDays($data["data"]["periodType"],$data["data"]["timeToElapse"]);
  $data["impact"]["dollarsInFlight"] = (int)impactDollarsInFlight($data["impact"]["infectionsByRequestedTime"],$rate,$amount,$period);
  $data["severeImpact"]["dollarsInFlight"] =  (int)impactDollarsInFlight($data["severeImpact"]["infectionsByRequestedTime"],$rate,$amount,$period); 
  // $data = json_encode($data);
  return $data;
}

function impactDollarsInFlight($infectionsByRequestedTime,$rate,$amount,$period){
  echo $infectionsByRequestedTime;
  echo $rate;
  echo $amount;
  echo $period;
  return $infectionsByRequestedTime * $rate *$amount *$period;
}