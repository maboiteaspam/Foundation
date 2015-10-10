<?php

namespace C\Intl;

use C\FS\LocalFs;
use Symfony\Component\Yaml\Yaml;

class YmlIntlLoader extends AbstractIntlFileLoader {


    public function __construct () {
        $this->ext = 'yml';
    }

    public function load ($filePath, $locale, $domain) {
        return Yaml::parse (LocalFs::file_get_contents ($filePath), true, false, true);
    }
}