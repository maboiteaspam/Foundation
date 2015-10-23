<?php
namespace C\ModernApp\File;

use C\Layout\Layout;

/**
 * Class AbstractStaticLayoutHelper
 * convenience class, extend and rewrite only the method that you need.
 *
 * @package C\ModernApp\File
 */
abstract class AbstractStaticLayoutHelper implements StaticLayoutHelperInterface{

    /**
     * @inheritdoc
     */
    public function executeMetaNode (Layout $layout, $nodeAction, $nodeContents) {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContents) {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function executeBlockNode (FileTransformsInterface $T, $subject, $nodeAction, $nodeContents) {
        return false;
    }
}
