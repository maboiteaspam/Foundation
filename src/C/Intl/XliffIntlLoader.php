<?php

namespace C\Intl;

use Symfony\Component\Translation\Loader\XliffFileLoader;

class XliffIntlLoader extends AbstractIntlFileLoader {


    public function __construct () {
        $this->ext = 'xlf';
    }

    public function load ($filePath, $locale, $domain) {
        $loader = new XliffFileLoader();
        return $loader->load($filePath, $locale, $domain)->all();
    }
}