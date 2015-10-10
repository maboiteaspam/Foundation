<?php

namespace C\Intl;

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

    protected $computed = [];
    public function getComputedFallbackLocales($locale) {
        if (!isset($this->computed[$locale])) {
            $this->computed[$locale] = $this->computeFallbackLocales($this->fallbackLocales, $locale);
        }
        return $this->computed[$locale];
    }

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
