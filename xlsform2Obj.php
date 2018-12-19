<?php
/**
 * Created by PhpStorm.
 * User: rulxphilome.alexis
 * Date: 12/13/2018
 * Time: 10:01 AM
 */
ini_set('display_errors', 1);

$xmlDoc = new DOMDocument();
//$xmlDoc->load("./ME18_Enquete_Structure_CS_v13.xml");
$xmlDoc->load("./ME18_Enquete_Structure_CS_v13.xml");
$allBind = $xmlDoc->getElementsByTagName('bind');
//var_dump($allBind);
$allQ = array('ME18_Enquete_Structure_CS_v13'=>array());
$questionsFromBind = array();

foreach ($allBind as $bb){
    $questionsFromBind[] =  substr($bb->getAttribute("nodeset"),1);
}

foreach ($questionsFromBind as $qString){
    $exPath = explode('/',$qString);
    $curArray = array();
    $flag = '';
    $selectorString = '';
    foreach ($exPath as $onePath){
        $selectorString .="['".$onePath."']";
        $select = "$"."curManip = &$"."allQ".$selectorString.";";
        eval($select);
        if(isset($curManip)){
            $curArray = $curManip;
        }else{
            $curArray[] = array($onePath => array());
        }

    }
}

$allQ['data_path'] = $questionsFromBind;




$allQ2UPDT = $allQ;

$xmlDoc_Data = new DOMDocument();
$xmlDoc_Data->load('./dataSample.xml');
$xpath = new DOMXPath($xmlDoc_Data);
$xpath->registerNamespace('zx', 'http://opendatakit.org/submissions');


foreach ($questionsFromBind as $qString){
    $exPath = explode('/',$qString);
    $selectorString = '';
    foreach ($exPath as $path){
        $selectorString .= "['".$path."']";
    }
    $qryPath = '';
    foreach($exPath as $thPath){
        $qryPath .= 'zx:'.$thPath.'/';
    }

    $select = "$"."rowData = &$"."allQ2UPDT".$selectorString.";";
    eval($select);

    if(is_null($rowData)){
        $tt =  '//'.$qryPath;
        $tt = rtrim($tt,'/');
        if($xpath->query($tt)->length > 0){
            $rowData = $xpath->query($tt)->item(0)->nodeValue;
        }else{

        }
    }
}

echo json_encode(
    array(
        "formDefinition" => $allQ,
        "withData" => $allQ2UPDT
));