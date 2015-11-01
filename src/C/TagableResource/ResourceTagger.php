<?php
namespace C\TagableResource;


class ResourceTagger{

    public $computers = [];

    public function __construct () {
        $this->computers['po'] = function ($value) {
            return $value;
        };
    }


    public function addTagComputer ($dataType, $computer) {
        $this->computers[$dataType] = $computer;
    }

    public function isFresh (TagedResource $res) {
        return $res->originalTag
        && $res->originalTag===$this->sign($res);
    }

    public function sign (TagedResource $resource) {
        $h = '';
        foreach ($resource->resources as $res) {
            $tagger = $res['type'];
            if (isset($this->computers[$tagger])) {
                $computer = $this->computers[$tagger];
                if ($computer instanceof TagResolverInterface ) {
                    $value = $computer->resolve($res['value']);
                } else {
                    $value = $computer($res['value']);
                }
                try{
                    $h .= serialize($value);
                }catch(\Exception $ex) {
                    echo $ex;
                }
            } else {
                // @todo make this exception configurable,
                // @todo in some case we need to be able to disable a module
                // @todo and that is blocking as it throws a killing app exception
                throw new \Exception("Missing tag computer type '$tagger'");
            }
        }
        $resource->originalTag = sha1($h);
        return $resource->originalTag;
    }
}