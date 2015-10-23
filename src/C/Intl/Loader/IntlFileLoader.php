<?php

namespace C\Intl\Loader;

use C\Intl\IntlFileLoaderInterface;

/**
 * Class IntlFileLoader
 * is a facade to load multiple file formats according to their extension.
 *
 * It gets File Format translator helpers attached to it,
 * It can then load a file with the appropriate format loader.
 *
 * @package C\Intl\Loader
 */
class IntlFileLoader {

    /**
     * An array of per file format loaders.
     *
     * @var array
     */
    public $loaders = [];

    /**
     * @param IntlFileLoaderInterface $loader
     */
    public function addLoader (IntlFileLoaderInterface $loader) {
        $this->loaders[] = $loader;
    }

    /**
     * Get the loader object given an extension.
     *
     * @param $ext
     * @return IntlFileLoaderInterface|null
     */
    public function getLoader ($ext) {
        foreach ($this->loaders as $loader) {
            /* @var $loader IntlFileLoaderInterface */
            if ($loader->isExt($ext)) {
                return $loader;
            }
        }
        return NULL;
    }

    /**
     * Tells if a file format can be loaded given it extension.
     *
     * @param $ext
     * @return bool
     */
    public function canLoad ($ext) {
        foreach ($this->loaders as $loader) {
            /* @var $loader IntlFileLoaderInterface */
            if ($loader->isExt($ext)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Appropriately load file.
     *
     * @param $file
     * @param $ext
     * @param $locale
     * @param $domain
     * @return mixed
     */
    public function loadFile ($file, $ext, $locale, $domain) {
        return $this->getLoader($ext)->load($file, $locale, $domain);
    }

    /**
     * Computes am INTL file name into a domain / locale information.
     * An INTL file should follow that naming convention
     *
     * [%domain%].%locale%.%extension%
     * validators_fr.yml
     * whatever_zh_TW.xlf
     *
     * @param $f
     * @param $ext
     * @return array
     */
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
