<?php
namespace C\View\Helper;

use C\Misc\Utils;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Class RoutingViewHelper
 * provides capabilities to generate urls
 * or url fragments
 *
 * @package C\View\Helper
 */
class RoutingViewHelper extends AbstractViewHelper {

    /**
     * @var UrlGenerator
     */
    public $generator;

    /**
     * @param UrlGenerator $generator
     */
    public function setUrlGenerator ( UrlGenerator $generator) {
        $this->generator = $generator;
    }

    /**
     * Given its name and parameters
     * return an url
     *
     * @param $name
     * @param array $options
     * @param array $only
     * @return string
     */
    public function urlFor ($name, $options=[], $only=[]) {
        $options = Utils::arrayPick($options, $only);
        return $this->generator->generate($name, $options);
    }

    /**
     * format args for a query string
     * ?a=b&c=d
     *
     * @param array $data
     * @param array $only
     * @return string
     */
    public function urlArgs ($data=[], $only=[]) {
        /* @var $block \C\Layout\Block */
        $block = $this->block;
        if (isset($block->meta['from'])) {
            $data = array_merge(Utils::arrayPick($block->meta, ['from']), $data); // @todo improve that mechanism
        }
        $data = Utils::arrayPick($data, $only);
        $query = http_build_query($data);
        return $query ? '?'.$query : '';
    }
}
