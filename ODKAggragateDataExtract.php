<?php

//require_once './vendor/guzzlehttp/guzzle/src/Client.php';
require_once 'vendor/autoload.php';
require_once './ODKAggregateForm.php';

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
class ODKAggragateDataExtract
{
    private $ODKAggregateUrl;
    private $username;
    private $password;

    const GET_FORMLIST_URI = 'xformsList';
    const GET_FORMDEFINITION_URI = 'formXml';
    const GET_FORMIDLIST_URI = 'view/submissionList';
    const GET_FORMDOWNLOAD_URI = 'view/downloadSubmission';

    public function __construct($ODKAggregateUrl,$username,$password)
    {
        $this->ODKAggregateUrl = $ODKAggregateUrl;
        $this->username = $username;
        $this->password = $password;
    }

    public function createDigestString($username, $password, $uri){
        $httpClient = new Client();
        try{
            $res = $httpClient->request('GET', $this->ODKAggregateUrl,array(
                'headers' => array(
                    'Authorization' => 'Digest realm=""'
                )
            ));
        }catch (GuzzleHttp\Exception\ClientException $e){
            $authResponse = $e->getResponse()->getHeader('WWW-Authenticate');
            $authResponse = explode(',', preg_replace("/^Digest/i", "", $authResponse[0]));
            $auth_pieces = array();
            foreach ($authResponse as &$piece) {
                $piece = trim($piece);
                $piece = explode('=', $piece);
                $auth_pieces[$piece[0]] = trim($piece[1], '"');
            }
            //building digest string
            $nc = str_pad('1', 8, '9', STR_PAD_LEFT);
            $cnonce = '0a4f113b';
            $A1 = md5("{$username}:{$auth_pieces['realm']}:{$password}");
            $A2 = md5("GET:".$uri);

            $auth_pieces['response'] = md5("{$A1}:{$auth_pieces['nonce']}:{$nc}:{$cnonce}:{$auth_pieces['qop']}:${A2}");
            $digest_header = "Digest username=\"{$username}\", realm=\"{$auth_pieces['realm']}\", nonce=\"{$auth_pieces['nonce']}\", uri=\"{$uri}\", cnonce=\"{$cnonce}\", nc={$nc}, qop=\"{$auth_pieces['qop']}\", response=\"{$auth_pieces['response']}\", opaque=\"\", algorithm=\"MD5\"";
            return $digest_header;
        }
    }

    public function getFormsList(){
        $httpClient = new \GuzzleHttp\Client();
        try{
            $res = $httpClient->request('GET', $this->ODKAggregateUrl.'/'.self::GET_FORMLIST_URI,array(
                'headers' => array(
                    'Authorization' => $this->createDigestString($this->username,$this->password,'/ODKAggregate/'.self::GET_FORMLIST_URI)
                )
            ));
            $listOfFormsXML = simplexml_load_string($res->getBody()->getContents());
            return json_encode(array(
                "formList" => json_decode(json_encode($listOfFormsXML),true)['xform']
            ));
        }catch (GuzzleHttp\Exception\ClientException $e){
            echo $e->getResponse()->getBody()->getContents();
            echo $e->getRequest()->getBody()->getContents();
            var_dump($e->getRequest()->getHeaders());
        }catch (GuzzleHttp\Exception\ConnectException $e){
            return json_encode(array(
               "error" => "Connection error",
                "information" => "Can't connect to the ODK Aggregate Server! Please check your internet connection.",
                "originalMessage"=>$e->getMessage()
            ));
        }

    }

    public function getFormDefinition(ODKAggregateForm $theForm){
        $httpClient = new \GuzzleHttp\Client();
        $res = $httpClient->request('GET', $this->ODKAggregateUrl.'/'.self::GET_FORMDEFINITION_URI,array(
            'query'=>array(
                'formId' =>$theForm->getFormID()
            ),
            'headers' => array(
                'Authorization' => $this->createDigestString($this->username,$this->password,'/ODKAggregate/'.self::GET_FORMDEFINITION_URI)
            )
        ));
        return $res->getBody()->getContents();
    }

    public function getFormDefinitionToJSON(ODKAggregateForm $theForm){
        $formDef = $this->getFormDefinition($theForm);
        $xmlDoc = new DOMDocument();
        $xmlDoc->loadXML($formDef);

        $allBind = $xmlDoc->getElementsByTagName('bind');
        $allQ = array();
        $questionsFromBind = array();
        $parentNodeName = explode("/",substr($allBind->item(0)->getAttribute("nodeset"),1))[0];
//        $xmlDoc_Data->loadXML($this->getInstancesOfForm($theForm,$uiid));
        $uiidValue = $xmlDoc->getElementsByTagName($parentNodeName)->item(0)->getAttribute("instanceID");
        $submissionDate = $xmlDoc->getElementsByTagName($parentNodeName)->item(0)->getAttribute("submissionDate");
        $isComplete = $xmlDoc->getElementsByTagName($parentNodeName)->item(0)->getAttribute("isComplete");
        $markedAsCompleteDate = $xmlDoc->getElementsByTagName($parentNodeName)->item(0)->getAttribute("markedAsCompleteDate");

        foreach ($allBind as $bb){
            $questionsFromBind[] =  substr($bb->getAttribute("nodeset"),1);
        }

        $questionsFromBind[] = $parentNodeName."/meta/submissionDate";
        $questionsFromBind[] = $parentNodeName."/meta/isComplete";
        $questionsFromBind[] = $parentNodeName."/meta/markedAsCompleteDate";

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
        return json_encode($allQ);
    }
    
    public function getFormInstanceValueToJSON($theForm, $uiid){
        $formDef = $this->getFormDefinition($theForm);
        $xmlDoc = new DOMDocument();
        $xmlDoc->loadXML($formDef);
        $allBind = $xmlDoc->getElementsByTagName('bind');

        $parentNodeName = explode("/",substr($allBind->item(0)->getAttribute("nodeset"),1))[0];

        $xmlDoc_Data = new DOMDocument();
//var_dump($this->getInstancesOfForm($theForm,$uiid));exit;
        $xmlDoc_Data->loadXML($this->getInstancesOfForm($theForm,$uiid));
        $uiidValue = $xmlDoc_Data->getElementsByTagName($parentNodeName)->item(0)->getAttribute("instanceID");
        $submissionDate = $xmlDoc_Data->getElementsByTagName($parentNodeName)->item(0)->getAttribute("submissionDate");
        $isComplete = $xmlDoc_Data->getElementsByTagName($parentNodeName)->item(0)->getAttribute("isComplete");
        $markedAsCompleteDate = $xmlDoc_Data->getElementsByTagName($parentNodeName)->item(0)->getAttribute("markedAsCompleteDate");

        $xpath = new DOMXPath($xmlDoc_Data);
        $xpath->registerNamespace('zx', 'http://opendatakit.org/submissions');

//        $allQ = array();
        $allQ = json_decode($this->getFormDefinitionToJSON($theForm),true);

//        var_dump($xmlDoc_Data);
        $questionsFromBind = array();

        foreach ($allBind as $bb){
            $questionsFromBind[] =  substr($bb->getAttribute("nodeset"),1);
        }
        $questionsFromBind[] = $parentNodeName."/meta/submissionDate";
        $questionsFromBind[] = $parentNodeName."/meta/isComplete";
        $questionsFromBind[] = $parentNodeName."/meta/markedAsCompleteDate";

        $allQ["data_path"] = $questionsFromBind;

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

            $select = "$"."rowData = &$"."allQ".$selectorString.";";
//            echo $select."<br />";
            eval($select);

            if(is_null($rowData)){
                $tt =  '//'.$qryPath;
                $tt = rtrim($tt,'/');
                if($xpath->query($tt)->length > 0){
                    $rowData = $xpath->query($tt)->item(0)->nodeValue;

                }else{
                    switch ($exPath[count($exPath)-1]){
                        case "instanceID" : {
                            $rowData = $uiidValue;
                            break;
                        }

                        case "submissionDate" : {
                            $rowData = $submissionDate;
                            break;
                        }

                        case "isComplete" : {
                            $rowData = ($isComplete == "true" ? "Oui" : "Non");
                            break;
                        }

                        case "markedAsCompleteDate" : {
                            $rowData = $markedAsCompleteDate;
                            break;
                        }
                    }

                }
            }
        }
        $allQ['uiid'] = $uiidValue;
        $allQ['submissionDate'] = $submissionDate;
        $allQ['isComplete'] = $isComplete;
        $allQ['markedAsCompleteDate'] = $markedAsCompleteDate;
        return json_encode($allQ);
    }

    public function getFormIdList(ODKAggregateForm $theForm){
        $httpClient = new \GuzzleHttp\Client();
        $res = $httpClient->request('GET', $this->ODKAggregateUrl.'/'.self::GET_FORMIDLIST_URI,array(
            'query'=>array(
                'formId' =>$theForm->getFormID(),
                'numEntries'=> 10000000,
                'cursor'=>''
            ),
            'headers' => array(
                'Authorization' => $this->createDigestString($this->username,$this->password, '/ODKAggregate/'.self::GET_FORMIDLIST_URI)
            )
        ));

        $listOfFormIDSXML = simplexml_load_string($res->getBody()->getContents());
        return json_encode(array(
            "formIdsList" => json_decode(json_encode($listOfFormIDSXML),true)['idList']['id']
        ));
    }

    public function getInstancesOfForm(ODKAggregateForm $theForm, $uriID){
        $httpClient = new \GuzzleHttp\Client();
        try{
//            echo $theForm->getFormID().'[@version='.((strlen($theForm->getVersion())==0) ? 'null' : $theForm->getVersion()).' and @uiVersion=null]/ME18_Enquete_Structure_CS_v13[@key=uuid:767ef9dc-d050-439b-a1b7-3778692529d8]';
            $res = $httpClient->request('GET', $this->ODKAggregateUrl.'/'.self::GET_FORMDOWNLOAD_URI,array(
                'query'=>array(
                    'formId' =>$theForm->getFormID().'[@version='.((strlen($theForm->getVersion())==0) ? 'null' : $theForm->getVersion()).' and @uiVersion=null]/'.$this->getTopElement($theForm,$this->getFormDefinition($theForm)).'[@key='.$uriID.']'
                ),
                'headers' => array(
                    'Authorization' => $this->createDigestString($this->username,$this->password, '/ODKAggregate/'.self::GET_FORMDOWNLOAD_URI)
                )
            ));
            return $res->getBody()->getContents();
        }catch (GuzzleHttp\Exception\ClientException $e){
//            echo $e->getResponse()->getBody()->getContents();
        }catch (GuzzleHttp\Exception\ServerException $e){
            echo $e->getResponse()->getBody()->getContents();
        }

    }

    public function getTopElement(ODKAggregateForm $theForm,$formDefnString){
        $xml = new DOMDocument();
        $xml->loadXML($formDefnString);
        $xpath = new DOMXPath($xml);
        $xpath->registerNamespace('zx', 'http://www.w3.org/2002/xforms');
        return ($xpath->query("//zx:*[@id='".$theForm->getFormID()."']")->item(0)->tagName == 'data' ? 'data' : $xpath->query("//zx:*[@id='".$theForm->getFormID()."']")->item(0)->tagName);
    }

    public function getAllFormDataToJSON(ODKAggregateForm $theForm){
        $listOfIds = json_decode($this->getFormIdList($theForm),true)['formIdsList'];

        $formInstances = array();
        if(is_array($listOfIds)){
            foreach ($listOfIds as $oneId){
                $formInstances [] = json_decode($this->getFormInstanceValueToJSON($theForm, $oneId),true);
            }
        }else{
            $formInstances [] =  json_decode($this->getFormInstanceValueToJSON($theForm, $listOfIds),true);
        }


        return json_encode($formInstances);

    }


    public function __toString()
    {
        // TODO: Implement __toString() method.
        return 'uri: '.$this->ODKAggregateUrl.' ; username: '.$this->username.' password: '.$this->password;
    }
}