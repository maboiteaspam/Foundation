<?php
namespace C\Repository;

use C\TagableResource\Ghoster;
use C\TagableResource\TagableResourceInterface;
use C\TagableResource\TagedResource;

/**
 * Class RepositoryGhoster
 * provides the ability to ghost
 * an application service data provider
 *
 * @package C\Repository
 */
class RepositoryGhoster extends Ghoster{

    /**
     * @param $ghosted
     * @param RepositoryGhoster|null $tag
     */
    public function __construct ($ghosted, RepositoryGhoster $tag=null) {
        parent::__construct($ghosted);
        $this->tag = $tag;
    }

    /**
     * @var Ghoster
     */
    public $tag;

    /**
     * @param Ghoster $tag
     * @return $this
     */
    public function setTag (Ghoster $tag) {
        $this->tag = $tag;
        return $this;
    }

    /**
     * @param null $asName
     * @return TagedResource|mixed
     * @throws \Exception
     */
    public function getTaggedResource($asName=null) {
        if (!$this->tag) {
            $res = new TagedResource();
            $res->addResource([$this->ghosted->getRepositoryName(), $this->methods],
                'repository', $asName);
        } else {
            $res = $this->tag->getTaggedResource();
        }

        foreach ($this->methods as $method) {
            $method_args = $method[1];
            foreach( $method_args as $index=>$arg) {
                if ($arg instanceof TagableResourceInterface) {
                    $res->addTaggedResource($arg->getTaggedResource(), $asName);
                }
            }
        }
        return $res;
    }

}
