<?php

namespace C\Intl;

interface IntlFileLoaderInterface {

    public function isExt ($ext);

    public function load ($file, $locale, $domain);

}