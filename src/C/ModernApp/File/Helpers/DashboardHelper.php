<?php
namespace C\ModernApp\File\Helpers;

use C\Layout\Transforms\Transforms;
use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\Dashboard\Transforms as Dashboard;
use C\ModernApp\File\FileTransformsInterface;

/**
 * Class DashboardHelper
 * Provides new block and structure action
 * - to display the dashboard in your view
 * - to configure debug method of specific blocks.
 *
 * @package C\ModernApp\File\Helpers
 */
class DashboardHelper extends  AbstractStaticLayoutHelper{

    /**
     * @var array
     */
    public $extensions = [];

    /**
     * @param $extensions
     * @return $this
     */
    public function setExtensions ($extensions) {
        $this->extensions = array_merge($this->extensions, $extensions);
        return $this;
    }

    /**
     * Provide a new structure action
     *      show_dashboard
     * to insert the dashboard into the layout.
     *
     * $options is an array of extension to display in the dashboard.
     *
     * - show_dashboard:
     *      - extension1
     *      - extension2
     *      - time_travel
     *
     * @param FileTransformsInterface $T
     * @param $nodeAction
     * @param $nodeContents
     * @return bool
     */
    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContents) {
        if ($nodeAction==="show_dashboard") {
            if ($T->getLayout()->debugEnabled) {
                Dashboard::transform()
                    ->setLayout($T->getLayout())
                    ->setExtensions($this->extensions)
                    ->show(__CLASS__, $nodeContents);
            }
            return true;
        }
    }

    /**
     * Provide a new block action
     *      debug_with
     * to configure the block to use either
     *      HTML comments
     *      c_block_node
     * method for debugging the layout in the view.
     *
     *  structure:
     *      block_id
     *          debug_with: comments
     *          debug_with: node
     *
     * @param FileTransformsInterface $T
     * @param $subject
     * @param $nodeAction
     * @param $nodeContents
     * @return bool
     */
    public function executeBlockNode (FileTransformsInterface $T, $subject, $nodeAction, $nodeContents) {
        if ($nodeAction==="debug_with") {
            if ($T->getLayout()->debugEnabled) {
                Transforms::transform()
                    ->setLayout($T->getLayout())
                    ->setDefaultMeta($subject, ['debug_with' => $nodeContents]);
            }
            return true;
        }
    }
}
