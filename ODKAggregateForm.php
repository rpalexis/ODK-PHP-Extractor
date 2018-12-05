<?php

/**
 * Created by PhpStorm.
 * User: rulxphilome.alexis
 * Date: 12/5/2018
 * Time: 2:37 PM
 */
class ODKAggregateForm
{
    private $formID;
    private $name;
    private $majorMinorVersion;
    private $version;
    private $hash;
    private $downloadUrl;
    private $topElement;


    public function __construct($formID, $name, $majorMinorVersion, $version, $hash, $downloadUrl)
    {
        $this->formID = $formID;
        $this->name = $name;
        $this->majorMinorVersion = $majorMinorVersion;
        $this->version = $version;
        $this->hash = $hash;
        $this->downloadUrl = $downloadUrl;
    }


    public function getFormID()
    {
        return $this->formID;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getMajorMinorVersion()
    {
        return $this->majorMinorVersion;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getDownloadUrl()
    {
        return $this->downloadUrl;
    }


    public function getTopElement()
    {
        return $this->topElement;
    }


    
    public function setFormID($formID)
    {
        $this->formID = $formID;
    }


    public function setName($name)
    {
        $this->name = $name;
    }

    public function setMajorMinorVersion($majorMinorVersion)
    {
        $this->majorMinorVersion = $majorMinorVersion;
    }


    public function setVersion($version)
    {
        $this->version = $version;
    }


    public function setHash($hash)
    {
        $this->hash = $hash;
    }


    public function setDownloadUrl($downloadUrl)
    {
        $this->downloadUrl = $downloadUrl;
    }


    public function setTopElement($topElement)
    {
        $this->topElement = $topElement;
    }
}