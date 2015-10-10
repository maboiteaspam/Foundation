<?php

namespace C\Intl;

use Moust\Silex\Cache\CacheInterface;

class IntlJitLoader {

    /**
     * @var CacheInterface
     */
    public $cache;
    public function setCache (CacheInterface $cache) {
        $this->cache = $cache;
    }

    /**
     * @var LocaleManager
     */
    public $localeMngr;
    public function setLocaleManager (LocaleManager $localeMngr) {
        $this->localeMngr = $localeMngr;
    }

    public function getLocaleManager(){
        return $this->localeMngr;
    }

    /**
     * @var array
     */
    public $resources = [];

    public function getMessage($id, $domain = null, $locale = null){
        $domain = $domain ? $domain : 'messages';
        $locale = $locale ? $locale : $this->localeMngr->getLocale();

        $this->fetchDomain($locale, $domain);
        if (isset($this->resources[$locale][$domain][$id])) {
            return $this->resources[$locale][$domain][$id];
        }

        $altLocale = $this->localeMngr->getComputedFallbackLocales($locale)[0];
        $this->fetchDomain($altLocale, $domain);
        if (isset($this->resources[$altLocale][$domain][$id])) {
            return $this->resources[$altLocale][$domain][$id];
        }

        $altLocaleBis = $this->localeMngr->getFallbackLocales()[0];
        $this->fetchDomain($altLocaleBis, $domain);
        if (isset($this->resources[$altLocaleBis][$domain][$id])) {
            return $this->resources[$altLocaleBis][$domain][$id];
        }
        return $id;
    }

    public function fetchDomain ($locale, $domain) {
        if (!array_key_exists($locale, $this->resources)) {
            $this->resources[$locale]= [];
        }
        if (!array_key_exists($domain, $this->resources[$locale])) {
            $d = $this->cache->fetch("all.translations.$locale.$domain");
            $this->resources[$locale][$domain] = $d===false?[]:$d['translations'];
        }
    }

    public function fetchWellKnownLocales () {
        return $this->cache->fetch("all.locales");
    }

    public function storeDomain ($locale, $domain, $translations) {
        return $this->cache->store("all.translations.$locale.$domain", [
            'locale'=>$locale,
            'domain'=>$domain,
            'translations'=>$translations
        ]);
    }

    public function storeAllKnownLocales ($locales) {
        return $this->cache->store("all.locales", $locales);
    }

    public function getAllKnownLocales () {
        return $this->cache->fetch("all.locales");
    }

}
