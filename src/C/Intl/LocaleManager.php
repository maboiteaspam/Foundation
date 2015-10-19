<?php

namespace C\Intl;

/**
 * Class LocaleManager helps to deal with locale selection.
 * Given a locale it can compute it to its closest alternative,
 * or a configured fallback.
 *
 * @package C\Intl
 */

class LocaleManager {

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var array
     */
    private $fallbackLocales = array();

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function setLocale($locale)
    {
        $this->assertValidLocale($locale);
        $this->locale = $locale;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function getLocale()
    {
        return $this->locale;
    }


    /**
     * Sets the fallback locales.
     *
     * @param array $locales The fallback locales
     *
     * @throws \InvalidArgumentException If a locale contains invalid characters
     *
     * @api
     */
    public function setFallbackLocales(array $locales)
    {
        foreach ($locales as $locale) {
            $this->assertValidLocale($locale);
        }

        $this->fallbackLocales = $locales;
    }

    /**
     * Gets the fallback locales.
     *
     * @return array $locales The fallback locales
     *
     * @api
     */
    public function getFallbackLocales()
    {
        return $this->fallbackLocales;
    }

    /**
     * Returns a coherent list of locales against a specific $locale.
     * if $locale is 'ee_BB', it will prepend 'ee'
     * to the returned array.
     *
     * @param $knownLocales
     * @param $locale
     * @return array
     */
    public function computeFallbackLocales($knownLocales, $locale)
    {
        $locales = array();
        foreach ($knownLocales as $fallback) {
            if ($fallback === $locale) {
                continue;
            }

            $locales[] = $fallback;
        }

        if (strrchr($locale, '_') !== false) {
            array_unshift($locales, substr($locale, 0, -strlen(strrchr($locale, '_'))));
        }

        return array_unique($locales);
    }

    /**
     * Returns a coherent list of locales against a specific $locale.
     * if $locale is 'ee_BB', it will prepend 'ee_BB' then 'ee'
     * to the returned array.
     *
     * @param $knownLocales
     * @param $locale
     * @return array
     */
    public function computeLocales($knownLocales, $locale)
    {
        $locales = array();
        foreach ($knownLocales as $fallback) {
            if ($fallback === $locale) {
                continue;
            }

            $locales[] = $fallback;
        }

        if (strrchr($locale, '_') !== false) {
            array_unshift($locales, substr($locale, 0, -strlen(strrchr($locale, '_'))));
        }
        array_unshift($locales, $locale);

        return array_unique($locales);
    }


    /**
     * Memoized version of computeLocales
     *
     * @param $locale
     * @return mixed
     */
    public function getComputedFallbackLocales($locale) {
        if (!isset($this->computed[$locale])) {
            $this->computed[$locale] = $this->computeFallbackLocales($this->fallbackLocales, $locale);
        }
        return $this->computed[$locale];
    }
    protected $computed = [];

    /**
     * Asserts that the locale is valid, throws an Exception if not.
     *
     * @param string $locale Locale to tests
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     */
    protected function assertValidLocale($locale)
    {
        if (1 !== preg_match('/^[a-z0-9@_\\.\\-]*$/i', $locale)) {
            throw new \InvalidArgumentException(sprintf('Invalid "%s" locale.', $locale));
        }
    }
}
