<?php
namespace C\View\Helper;

use C\Layout\Block;
use C\Misc\Utils;

/**
 * Class AssetsViewHelper
 * provides to inject assets as html inline
 * or external script to a specified layout target
 *
 * @package C\View\Helper
 */
class AssetsViewHelper implements ViewHelperInterface {


    /**
     * @var Block
     */
    public $block;
    /**
     * the content of the current inline opened with
     * inlineTo()
     *
     * @var string
     */
    public $currentInline;

    /**
     * @param Block $block
     */
    public function setBlockToRender (Block $block) {
        $this->block = $block;
//        if ($this->currentInline!==null)
        // the developer forgot to call $this->>endInline()
        $this->currentInline = null;
    }

    public $assetPatterns = [];
    /**
     * Patterns used to determine the
     * values of urlAsset()
     *
     * @param $patterns
     */
    public function setPatterns ($patterns) {
        $this->assetPatterns = $patterns;
    }
    /**
     * @param $pattern
     */
    public function addPattern ($pattern) {
        $this->assetPatterns[] = $pattern;
    }


    /**
     * Get url of an asset
     * given it s name and its parameters.
     *
     * @param $name
     * @param array $options
     * @param array $only
     * @return mixed|string
     */
    public function urlAsset ($name, $options=[], $only=[]) {
        $url = '';
        $imgUrls = $this->assetPatterns;
        if (isset($imgUrls[$name])) {
            $options = Utils::arrayPick($options, $only);
            $url = $imgUrls[$name];
            foreach ($options as $name => $o) {
                $url = str_replace(':'.$name, $o, $url);
            }
        }
        return $url;
    }


    /**
     * starts recording of a script/css inline.
     * $target is first head foot last
     *
     * Recorded block will be displayed as-is in the defined $target block.
     *
     * @param $target
     * @throws \Exception
     */
    public function inlineTo ($target) {
        $this->currentInline = $target;
        if (!in_array($target, ['first','head','foot','last',]))
            throw new \Exception('target must be one of first / head / foot / last');
//        if ($this->currentInline!==null) echo 'bad';
        // the developer forgot to call $this->>endInline()
        ob_start();
    }

    /**
     * End recording of an asset block.
     */
    public function endInline() {
//        if ($this->currentInline===null) echo 'bad';
        // the developer forgot to call $this->>inlineTo()
        $content = ob_get_clean();
        $type = strpos($content, "script")!==false?"js":"css";
        $this->block->addInline($this->currentInline, $type, $content);
        $this->currentInline = null;
    }

    /**
     * Inject into target the given asset path
     *
     * @param $target
     * @param $asset
     * @param bool $first
     */
    public function useAsset($target, $asset, $first=false) {
        $this->block->addAssets([$target=>[$asset]], $first);
    }


}
