<?php
namespace C\View\Helper;

use C\Layout\Layout;

/**
 * Class LayoutViewHelper
 * provides the ability to display the content
 * of the given block id.
 *
 * @package C\View\Helper
 */
class LayoutViewHelper extends AbstractViewHelper {

    /**
     * @var Layout
     */
    public $layout;

    /**
     * @param Layout $layout
     */
    public function setLayout ( Layout $layout) {
        $this->layout = $layout;
    }

    /**
     * Call to display a sub block.
     * @param $blockId
     * @param bool $force
     */
    public function display ($blockId, $force=false) {
        $layout = $this->layout;
        $shown = $layout->registry->has($blockId);
        if ($force) $layout->getOrCreate($blockId);
        $this->block->registerDisplayedBlock($blockId, $shown);
        if ($shown) $layout->registry->get($blockId)->setParentRenderBlock($this->block->id);
        echo "<!-- placeholder for block $blockId -->";
    }
}
