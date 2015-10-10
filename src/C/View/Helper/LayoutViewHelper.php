<?php
namespace C\View\Helper;

use C\Layout\Layout;

class LayoutViewHelper extends AbstractViewHelper {

    /**
     * @var Layout
     */
    public $layout;

    public function setLayout ( Layout $layout) {
        $this->layout = $layout;
    }

    public function display ($blockId, $force=false) {
        $layout = $this->layout;
        $shown = $layout->registry->has($blockId);
        if ($force) $layout->getOrCreate($blockId);
        $this->block->registerDisplayedBlock($blockId, $shown);
        if ($shown) $layout->registry->get($blockId)->setParentRenderBlock($this->block->id);
        echo "<!-- placeholder for block $blockId -->";
    }
}
