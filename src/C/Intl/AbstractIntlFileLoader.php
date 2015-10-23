<?php

namespace C\Intl;

/**
 * Class AbstractIntlFileLoader
 * A convenience class.
 *
 * @package C\Intl
 */
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
