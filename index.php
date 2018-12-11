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
header("Content-Type: application/json; charset=UTF-8");
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

        default :{
            return json_encode(array(
                'msg' => 'default behavior'
            ));
            break;
        }

    }
}