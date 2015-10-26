<?php
namespace C\View\Helper;

use C\Layout\Block;
use C\View\Env;

/**
 * Class AbstractViewHelper
 * Convenience class for a context view helper
 *
 * @package C\View\Helper
 */
abstract class AbstractViewHelper implements ViewHelperInterface{
    /**
     * @var Block
     */
    public $block;

    /**
     * It s the block the view is going to render
     * @param Block $block
     */
    public function setBlockToRender ( Block $block) {
        $this->block = $block;
    }
    /**
     * @var Env
     */
    public $env;

    /**
     * The system env.
     *
     * @param Env $env
     */
    public function setEnv ( Env $env) {
        $this->env = $env;
    }

}