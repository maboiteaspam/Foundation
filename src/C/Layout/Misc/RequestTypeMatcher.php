<?php

namespace C\Layout\Misc;

use C\TagableResource\TagableResourceInterface;
use C\TagableResource\TagedResource;


// @notes it s not stable interface at all so far.


class RequestTypeMatcher implements TagableResourceInterface{

    /**
     * default|ajax|esi
     * @var string
     */
    public $requestKind;
    /**
     * mobile|desktop
     * @var string
     */
    public $deviceType;
    /**
     * fr|en|default
     * @var string
     */
    public $langPreferred;

    public function __construct (){
        $this->requestKind      = 'get';
        $this->deviceType       = 'desktop';
        $this->langPreferred    = 'en';
    }

    public function setRequestKind($kind){
        $this->requestKind = $kind;
    }
    public function setDevice($device){
        $this->deviceType = $device;
    }
    public function setLang($lang){
        $this->langPreferred = $lang;
    }

    public function testValue ($knownValue, $expectedValue, $anyValue=null) {
        $negate = false;
        if (substr($expectedValue, 0, 1)==='!') {
            $negate = true;
            $expectedValue = substr($expectedValue, 1);
        }
        $match = ($anyValue && $expectedValue===$anyValue) || $knownValue===$expectedValue;
        $match = $negate ? !$match : $match;
        return $match;
    }

    public function isRequestKind ($kind) {
        if (is_string($kind)) $kind = explode(' ', $kind);
        foreach ($kind as $k) {
            if (!$this->testValue($this->requestKind, $k, 'any')) {
                return false;
            }
        }
        return true;
    }

    public function isDevice($device) {
        if (is_string($device)) $device = explode(' ', $device);
        foreach ($device as $d) {
            if (!$this->testValue($this->deviceType, $d, 'any')) {
                return false;
            }
        }
        return true;
    }

    public function isLang($language) {
        if (is_string($language)) $language = explode(' ', $language);
        foreach ($language as $l) {
            if (!$this->testValue($this->langPreferred, $l)) {
                return false;
            }
        }
        return true;
    }

    public function isFacets($facets) {
        if (isset($facets['device'])
            && !$this->isDevice($facets['device']))
            return false;
        if (isset($facets['lang'])
            && !$this->isLang($facets['lang']))
            return false;
        if (isset($facets['request'])
            && !$this->isRequestKind($facets['request']))
            return false;
        return true;
    }

    /**
     * @return TagedResource
     * @throws \Exception
     */
    public function getTaggedResource() {
        $res = new TagedResource();
        $res->addResource($this->requestKind);
        $res->addResource($this->deviceType);
        $res->addResource($this->langPreferred);
        return $res;
    }
}