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


$testForm = new ODKAggregateForm('ME18_Enquete_Structure_CS_v13','ME18_Enquete_Structure_CS_v13','','','md5:df739fbc3af4e26776489f00fe032b2d','http://186.1.203.54:8079/ODKAggregate/formXml?formId=ME18_Enquete_Structure_CS_v13');

$odkagg = new ODKAggragateDataExtract('http://pamsi.info:8079/ODKAggregate','vam_admin','V@madm!n02K');
//echo  $odkagg->getFormsList();

//echo $odkagg->getFormDefinition($testForm);
//echo "<pre>";
echo $odkagg->getFormDefinition($testForm);
//var_dump(($odkagg->getFormDefinition($testForm)));
//echo $odkagg->getTopElement($odkagg->getFormDefinition($testForm));
//var_dump($odkagg->getTopElement($odkagg->getFormDefinition($testForm)));
//echo "</pre>";
//echo "<pre>";
//var_dump(json_decode(json_encode($odkagg->getFormIdList($testForm)),true)['idList']['id']);
//echo "</pre>";
//echo $odkagg->getFormIdList($testForm);
//echo json_decode(json_encode($xmlOBJ),true);


//var_dump(json_decode(json_encode($xmlOBJ),true)['xform']);
//var_dump());
/*foreach ($xmlOBJ->attributes() as $t){
    var_dump($t);
}*/
/*echo "<pre>";
var_dump($odkagg->getFormsList());
echo "</pre>";*/