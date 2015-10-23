<?php

namespace C\Intl\Format;

use C\FS\LocalFs;
use C\Intl\AbstractIntlFileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * Class YmlIntlLoader
 * Can load a YAML formatted file and translate it to an array.
 *
 * @package C\Intl\Format
 */
class YmlIntlLoader extends AbstractIntlFileLoader {


    /**
     * Sets extension to yml.
     */
    public function __construct () {
        $this->ext = 'yml';
    }

    /**
     * Parse and load a YAML file.
     *
     *
     * @param $filePath
     * @param $locale
     * @param $domain
     * @return array
     */
    public function load ($filePath, $locale, $domain) {
        return Yaml::parse (LocalFs::file_get_contents ($filePath), true, false, true);
    }
}