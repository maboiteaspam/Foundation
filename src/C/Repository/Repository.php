<?php

namespace C\Repository;

/**
 * Class Repository
 * is the base class for a repository data implementation provider
 * it helps to identify a service within an app object.
 *
 * @package C\Repository
 */
abstract class Repository implements RepositoryInterface{

    public $repositoryName;
    public function setRepositoryName ($name) {
        $this->repositoryName = $name;
    }
    public function getRepositoryName () {
        return $this->repositoryName;
    }

}