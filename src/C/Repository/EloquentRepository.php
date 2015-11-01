<?php

namespace C\Repository;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Class EloquentRepository
 * is the base class for an
 * eloquent service data provider
 *
 * @package C\Repository
 */
abstract class EloquentRepository extends Repository{

    /**
     * @var \Illuminate\Database\Capsule\Manager
     */
    public $capsule;

    /**
     * @param Capsule $capsule
     */
    public function setCapsule(Capsule $capsule) {
        $this->capsule = $capsule;
    }
}