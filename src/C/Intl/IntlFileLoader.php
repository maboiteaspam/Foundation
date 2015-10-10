<?php

namespace C\Intl;

class IntlFileLoader {

    /**
     * @var array
     */
    public $loaders = [];

    public function addLoader (IntlFileLoaderInterface $loader) {
        $this->loaders[] = $loader;
    }

    public function getLoader ($ext) {
        foreach ($this->loaders as $loader) {
            /* @var $loader IntlFileLoaderInterface */
            if ($loader->isExt($ext)) {
                return $loader;
            }
        }
    }

    public function canLoad ($ext) {
        foreach ($this->loaders as $loader) {
            /* @var $loader IntlFileLoaderInterface */
            if ($loader->isExt($ext)) {
                return true;
            }
        }
    }

    public function loadFile ($file, $ext, $locale, $domain) {
        return $this->getLoader($ext)->load($file, $locale, $domain);
    }

    public function fileNameToIntl ($f, $ext) {
        $name = basename($f, ".$ext");
        $name = explode('.', $name);
        if (count($name)===1) {
            $domain = 'messages';
            $locale = $name[0];
        } else {
            $domain = $name[0];
            $locale = $name[1];
        }
        return [
            'intl'      => $f,
            'locale'    => $locale,
            'domain'    => $domain,
        ];
    }
}
