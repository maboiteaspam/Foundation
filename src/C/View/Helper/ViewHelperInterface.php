<?php
namespace C\View\Helper;

use C\Layout\Block;

/**
 * Interface ViewHelperInterface
 *
 * @package C\View\Helper
 */
interface ViewHelperInterface {

    /**
     * Set the block the view context
     * is about to render
     *
     * @param Block $block
     */
    public function setBlockToRender (Block $block);

}
