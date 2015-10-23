<?php
namespace C\ModernApp\File;

use C\Layout\Layout;

/**
 * Interface StaticLayoutHelperInterface
 * defines required methods for a
 * file layout helper.
 *
 * @package C\ModernApp\File
 */
interface StaticLayoutHelperInterface {

    /**
     * This method aims to execute transforms on the layout property object.
     *
     * It must return true if it has handled the action.
     * False otherwise.
     *
     * @param Layout $layout
     * @param $nodeAction
     * @param $nodeContents
     * @return mixed
     */
    public function executeMetaNode (Layout $layout, $nodeAction, $nodeContents);

    /**
     * This methods aims to execute transforms on the structure of the layout.
     *
     * It must return true if it has handled the action.
     * False otherwise.
     *
     * @param FileTransformsInterface $T
     * @param $nodeAction
     * @param $nodeContents
     * @return FileTransformsInterface
     */
    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContents);

    /**
     * This aims to execute action on a specified block id.
     *
     * It must return true if it has handled the action.
     * False otherwise.
     *
     * @param FileTransformsInterface $T
     * @param $blockTarget
     * @param $nodeAction
     * @param $nodeContents
     * @return mixed
     */
    public function executeBlockNode (FileTransformsInterface $T, $blockTarget, $nodeAction, $nodeContents);

}
