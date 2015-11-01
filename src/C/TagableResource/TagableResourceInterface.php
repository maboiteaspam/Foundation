<?php
namespace C\TagableResource;

interface TagableResourceInterface{

    /**
     * @return TagedResource
     * @throws \Exception
     */
    public function getTaggedResource ();

}
