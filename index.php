<?php
/**
 * Created by PhpStorm.
 * User: rulxphilome.alexis
 * Date: 12/5/2018
 * Time: 11:27 AM
 */
ini_set('display_errors', 1);

require_once './ODKAggragateDataExtract.php';
require_once  './ODKAggregateForm.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8",true);


if(isset($_POST['action'])){
    switch ($_POST['action']){
        case 'get_form_list': {
            //remoteSVR
            //username
            //password
            if(isset($_POST['remoteSVR']) AND isset($_POST['username']) AND isset($_POST['password'])){
                $odkLink = new ODKAggragateDataExtract($_POST['remoteSVR'],$_POST['username'],$_POST['password']);
                $listOfForms = $odkLink->getFormsList();
                unset($odkLink);
                echo $listOfForms;
            }else{
                echo "Can't find the required parameter to retrive List of forms";
            }
            break;
        }
        case 'get_form_definition':{
            //remoteSVR
            //username
            //password
            //formID
            //name
            //majorMinorVersion
            //version
            //hash
            //downloadUrl
            if(isset($_POST['remoteSVR']) AND isset($_POST['username']) AND isset($_POST['password']) AND isset($_POST['formID']) AND isset($_POST['name']) AND isset($_POST['majorMinorVersion']) AND isset($_POST['version']) AND isset($_POST['password']) AND isset($_POST['hash'])){
                $odkLink = new ODKAggragateDataExtract($_POST['remoteSVR'],$_POST['username'],$_POST['password']);
                $formsInfo = new ODKAggregateForm($_POST['formID'],$_POST['name'],$_POST['majorMinorVersion'],$_POST['version'],$_POST['hash'],$_POST['downloadUrl']);
                $formMSG = $odkLink->getFormDefinition($formsInfo);
                unset($odkLink);
                unset($formsInfo);
                echo $formMSG;
            }else{
                echo "Can't find the required parameter to retrive the form's definition";
            }
            break;
        }
        case 'get_form_ids_list':{
            //remoteSVR
            //username
            //password
            //formID
            //name
            //majorMinorVersion
            //version
            //hash
            //downloadUrl
            if(isset($_POST['remoteSVR']) AND isset($_POST['username']) AND isset($_POST['password']) AND isset($_POST['formID']) AND isset($_POST['name']) AND isset($_POST['majorMinorVersion']) AND isset($_POST['version']) AND isset($_POST['password']) AND isset($_POST['hash'])){
                $odkLink = new ODKAggragateDataExtract($_POST['remoteSVR'],$_POST['username'],$_POST['password']);
                $formsIdsList = new ODKAggregateForm($_POST['formID'],$_POST['name'],$_POST['majorMinorVersion'],$_POST['version'],$_POST['hash'],$_POST['downloadUrl']);
                $formsIDs = $odkLink->getFormIdList($formsIdsList);
                unset($odkLink);
                unset($formsIdsList);
                echo $formsIDs;
            }else{
                echo "Can't find the required parameter to retrive the form's ids list";
            }
            break;
        }

        default :{
            return json_encode(array(
                'msg' => 'default behavior'
            ));
            break;
        }

    }
}