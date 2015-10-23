<?php
namespace C\Intl;

use C\Intl\Loader\IntlJitLoader;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Translation\MessageSelector;

/**
 * Class Translator
 * Is the main facade object to translate messages.
 * Given a message id, it translate it to the best locale available.
 *
 * For that matter it consumes the JITLoader to read translations from cache,
 * It consumes the LocaleManager to identify the best locale,
 * It finally relies on message selector to select the best messages when a number of items is involved.
 *
 * @package C\Intl
 */
class Translator implements TranslatorInterface
{
    /**
     * @var MessageSelector
     */
    private $selector;


    /**
     * @var bool
     */
    private $debug;

    /**
     * Constructor.
     *
     * @param MessageSelector|null $selector The message selector for pluralization
     * @param bool                 $debug    Use cache in debug mode ?
     *
     * @throws \InvalidArgumentException If a locale contains invalid characters
     *
     * @api
     */
    public function __construct(MessageSelector $selector = null,  $debug = false)
    {
        $this->selector = $selector ?: new MessageSelector();
        $this->debug = $debug;
    }


    /**
     * Sets the preferred locale.
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->jitLoader->getLocaleManager()->setLocale($locale);
    }

    /**
     * Get preferred locale.
     *
     * @return mixed
     */
    public function getLocale()
    {
        return $this->jitLoader->getLocaleManager()->getLocale();
    }

    /**
     * @var IntlJitLoader
     */
    public $jitLoader;

    /**
     * @param IntlJitLoader $jitLoader
     */
    public function setJitLoader (IntlJitLoader $jitLoader) {
        $this->jitLoader = $jitLoader;
    }



    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        if (null === $domain) {
            $domain = 'messages';
        }

        $template = $this->jitLoader->getMessage($id, $domain, $locale);

        return strtr($template, $parameters);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        if (null === $domain) {
            $domain = 'messages';
        }

        $id = (string) $id;

        $template = $this->jitLoader->getMessage($id, $domain, $locale);

        return strtr($this->selector->choose($template, (int) $number, $locale), $parameters);
    }
}
