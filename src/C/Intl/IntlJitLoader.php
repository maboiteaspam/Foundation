<?php

namespace C\Intl;

use Moust\Silex\Cache\CacheInterface;

/**
 * Class IntlJitLoader
 * helps to store, fetch translations from/into cache.
 *
 * @package C\Intl
 */
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

    /**
     * Get translated message for given id.
     * If $domain is not provided, it fallback to messages.
     * If $locale is not provided, it fallback to the locale manager current locale.
     * If the message can t be found within the locale manager locale,
     * system tries to compute it to an alternate locale (zh_TW =to> zh).
     * If the message still can t be found, the system tries to use pre-configured
     * locales fallback.
     * Finally, if nothing can be found, it returns the provided message id.
     *
     * @param $id
     * @param null $domain
     * @param null $locale
     * @return mixed
     */
    public function getMessage($id, $domain = null, $locale = null){
        $domain = $domain ? $domain : 'messages';
        $locale = $locale ? $locale : $this->localeMngr->getLocale();

        $this->fetchDomain($locale, $domain);
        if (isset($this->resources[$locale][$domain][$id])) {
            return $this->resources[$locale][$domain][$id];
        }

        $computedFallbackLocales = $this->localeMngr->getComputedFallbackLocales($locale);
        if (isset($computedFallbackLocales[0])) {
            $altLocale = $computedFallbackLocales[0];
            $this->fetchDomain($altLocale, $domain);
            if (isset($this->resources[$altLocale][$domain][$id])) {
                return $this->resources[$altLocale][$domain][$id];
            }
        }

        $fallbackLocales = $this->localeMngr->getFallbackLocales();
        if (isset($fallbackLocales[0])) {
            $altLocaleBis = $fallbackLocales[0];
            $this->fetchDomain($altLocaleBis, $domain);
            if (isset($this->resources[$altLocaleBis][$domain][$id])) {
                return $this->resources[$altLocaleBis][$domain][$id];
            }
        }
        return $id;
    }

    /**
     * Fetches all messages for the given domain and locale.
     * @param $locale
     * @param $domain
     */
    public function fetchDomain ($locale, $domain) {
        if (!array_key_exists($locale, $this->resources)) {
            $this->resources[$locale]= [];
        }
        if (!array_key_exists($domain, $this->resources[$locale])) {
            $d = $this->cache->fetch("all.translations.$locale.$domain");
            $this->resources[$locale][$domain] = $d===false?[]:$d['translations'];
        }
    }

    /**
     * Provide an array of all locale declared
     * within the translation files
     *
     * @return mixed
     */
    public function fetchWellKnownLocales () {
        return $this->cache->fetch("all.locales");
    }

    /**
     * Store translations to cache
     * for a given $domain and its $locale.
     *
     * @param $locale
     * @param $domain
     * @param $translations
     * @return mixed
     */
    public function storeDomain ($locale, $domain, $translations) {
        return $this->cache->store("all.translations.$locale.$domain", [
            'locale'=>$locale,
            'domain'=>$domain,
            'translations'=>$translations
        ]);
    }

    /**
     * Store all locales declared with the translation files.
     *
     * @param $locales
     * @return mixed
     */
    public function storeAllKnownLocales ($locales) {
        return $this->cache->store("all.locales", $locales);
    }

}
