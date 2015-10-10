<?php

namespace C\Intl;

abstract class AbstractIntlFileLoader implements IntlFileLoaderInterface {

    /**
     * @var string
     */
    public $ext;

    public function isExt ($ext) {
        return $ext===$this->ext;
    }

    public abstract function load ($file, $locale, $domain);
}