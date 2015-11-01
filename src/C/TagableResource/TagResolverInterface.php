<?php
namespace C\TagableResource;


interface TagResolverInterface{
    /**
     * Resolve a tag into a computable value.
     * @return mixed
     */
    public function resolve ($mixed);
}
