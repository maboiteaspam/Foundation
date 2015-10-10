<?php

// again, imported rom symfony. But it is stripped of its original loader / cache capabilities.

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace C\Intl;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Translation\MessageSelector;

/**
 * Translator.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
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
     * {@inheritdoc}
     *
     * @api
     */
    public function setLocale($locale)
    {
        $this->jitLoader->getLocaleManager()->setLocale($locale);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function getLocale()
    {
        return $this->jitLoader->getLocaleManager()->getLocale();
    }

    /**
     * @var IntlJitLoader
     */
    public $jitLoader;
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
