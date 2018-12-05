<?php

//require_once './vendor/guzzlehttp/guzzle/src/Client.php';
require_once 'vendor/autoload.php';
require_once './ODKAggregateForm.php';
class ODKAggragateDataExtract
{
    private $ODKAggregateUrl;
    private $username;
    private $password;

    const GET_FORMLIST_URI = 'xformsList';
    const GET_FORMDEFINITION_URI = 'formXml';
    const GET_FORMIDLIST_URI = 'view/submissionList';

    public function __construct($ODKAggregateUrl,$username,$password)
    {
        $this->ODKAggregateUrl = $ODKAggregateUrl;
        $this->username = $username;
        $this->password = $password;
    }

    public function getFormsList(){
        $httpClient = new \GuzzleHttp\Client();
        $res = $httpClient->request('GET', $this->ODKAggregateUrl.'/'.self::GET_FORMLIST_URI,array(
            'auth'=> array(
                $this->username,
                $this->password,
                'digest'
            )
        ));
        $listOfFormsXML = simplexml_load_string($res->getBody()->getContents());
        return json_encode(array(
            "formList" => json_decode(json_encode($listOfFormsXML),true)['xform']
        ));

    }

    public function getFormDefinition(ODKAggregateForm $theForm){
        $httpClient = new \GuzzleHttp\Client();
        $res = $httpClient->request('GET', $this->ODKAggregateUrl.'/'.self::GET_FORMDEFINITION_URI,array(
            'auth'=> array(
                $this->username,
                $this->password,
                'digest'
            ),
            'query'=>array(
                'formId' =>$theForm->getFormID()
            )
        ));

        return $res->getBody()->getContents();
    }

    public function getFormIdList(ODKAggregateForm $theForm){
        $httpClient = new \GuzzleHttp\Client();
        $res = $httpClient->request('GET', $this->ODKAggregateUrl.'/'.self::GET_FORMIDLIST_URI,array(
            'auth'=> array(
                $this->username,
                $this->password,
                'digest'
            ),
            'query'=>array(
                'formId' =>$theForm->getFormID(),
                'numEntries'=> 100,
                'cursor'=>''
            ),
            'headers' => array(
                'Authorization' => 'Digest username="vam_admin", realm="odk_server ODK Aggregate", nonce="", uri="/ODKAggregate/view/submissionList", algorithm="MD5", response="2da45d44dde23b6e06e0f4b8755927a7"'
            )
        ));
        $listOfFormIDSXML = simplexml_load_string($res->getBody()->getContents());
        return json_encode(array(
            "formIdsList" => json_decode(json_encode($listOfFormIDSXML),true)['idList']['id']
        ));
    }

    public function getInstancesOfForm(ODKAggregateForm $theForm){
        $httpClient = new \GuzzleHttp\Client();
        $res = $httpClient->request('GET', $this->ODKAggregateUrl.'/'.self::GET_FORMIDLIST_URI,array(
            'auth'=> array(
                $this->username,
                $this->password,
                'digest'
            ),
            'query'=>array(
                'formId' =>$theForm->getFormID().'[@version='.((strlen($theForm->getVersion())==0) ? 'null' : $theForm->getVersion()).'and @uiVersion=null]/PROCSIMAST_V3_Bassi_Bleu_1_251118[@key=uuid:767ef9dc-d050-439b-a1b7-3778692529d8]'
            ),
            'headers' => array(
                'Authorization' => 'Digest username="vam_admin", realm="odk_server ODK Aggregate", nonce="", uri="/ODKAggregate/view/submissionList", algorithm="MD5", response="2da45d44dde23b6e06e0f4b8755927a7"'
            )
        ));
    }

    public function getTopElement($formDefnString){
        return simplexml_load_string($formDefnString);
    }


    public function __toString()
    {
        // TODO: Implement __toString() method.
        return 'uri: '.$this->ODKAggregateUrl.' ; username: '.$this->username.' password: '.$this->password;
    }
}