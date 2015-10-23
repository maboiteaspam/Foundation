<?php

namespace C\Intl;

/**
 * Interface IntlFileLoaderInterface
 *
 * Describe interface required by file formats loader.
 *
 * @package C\Intl
 */
interface IntlFileLoaderInterface {

    /**
     * Tell if the given file extension matches this loader object.
     *
     * @param $ext
     * @return mixed
     */
    public function isExt ($ext);

    /**
     * Load a file given its file name.
     *
     * @param $file
     * @param $locale @unused Lands here to be compatible with JITLoader
     * @param $domain @unused Lands here to be compatible with JITLoader
     * @return mixed
     */
    public function load ($file, $locale, $domain);

}