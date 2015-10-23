<?php

namespace C\Watch;

use C\Intl\Loader\IntlJitLoader;
use C\Intl\Loader\IntlFileLoader;

class WatchedIntl extends WatchedRegistry {

    /**
     * @var IntlFileLoader
     */
    public $loader;

    public function setLoader (IntlFileLoader $loader) {
        $this->loader = $loader;
    }

    /**
     * @var IntlJitLoader
     */
    public $jitLoader;

    public function setJitLoader (IntlJitLoader $jitLoader) {
        $this->jitLoader = $jitLoader;
    }

    public function clearCache (){
        parent::clearCache();
    }

    public function build () {
        parent::build();
        return $this->buildTranslations();
    }

    public function changed ($action, $file) {
        $updated = parent::changed($action, $file);
        $this->buildTranslations();
        return $updated;
    }

    public function buildTranslations () {
        $loader = $this->loader;
        $all = [];
        $this->registry->each(function ($item) use(&$all, $loader) {
            if($loader->canLoad($item['extension'])) {
                $intl = $loader->fileNameToIntl($item['absolute_path'], $item['extension']);
                $locale = $intl['locale'];
                $domain = $intl['domain'];
                if (!isset($all[$locale])) $all[$locale] = [];
                if (!isset($all[$locale][$domain])) $all[$locale][$domain] = [];
                try{
                    $all[$locale] = array_merge($all[$locale][$domain], [
                        $domain=> $loader->loadFile($item['absolute_path'], $item['extension'], $locale, $domain)
                    ]);
                }catch(\Exception $ex) {
                    throw new \Exception("Failed to parse file ".$item['absolute_path'], 0, $ex);
                }
            }
        });
        foreach ($all as $locale=>$domainTranslations) {
            foreach ($domainTranslations as $domain=>$translations) {
                $this->jitLoader->storeDomain($locale, $domain, $translations);
            }
        }
        $this->jitLoader->storeAllKnownLocales(array_keys($all));
        return $this;
    }

}