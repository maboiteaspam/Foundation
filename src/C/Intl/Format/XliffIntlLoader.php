<?php

namespace C\Intl\Format;

use C\Intl\AbstractIntlFileLoader;
use Symfony\Component\Translation\Loader\XliffFileLoader;

/**
 * Class XliffIntlLoader
 * Can load an XLF file and translate it to a php array.
 *
 * @package C\Intl\Format
 */
class XliffIntlLoader extends AbstractIntlFileLoader {

    /**
     * Set extension to xlf
     */
    public function __construct () {
        $this->ext = 'xlf';
    }

    /**
     * Load an XLF file format and return an array.
     *
     * @param $filePath
     * @param $locale
     * @param $domain
     * @return array
     */
    public function load ($filePath, $locale, $domain) {
        $loader = new XliffFileLoader();
        return $loader->load($filePath, $locale, $domain)->all();
    }
}