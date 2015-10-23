<?php
namespace C\ModernApp\File\Helpers;

use C\Layout\Transforms\Transforms;
use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\File\FileTransformsInterface;

/**
 * Class AssetsHelper
 * Provide new block actions to add / remove / replace assets.
 *
 *
 * @package C\ModernApp\File\Helpers
 */
class AssetsHelper extends AbstractStaticLayoutHelper{

    /**
     *
     * Provide new block actions to add / remove / replace assets.
     *
     * when nodeAction is add / remove
     * $options is an array of [
     *  $targets => [
     *      assets,
     *      assets
     *  ]
     * ]
     *
     * when node action is replace
     * $options is an array of [
     *  $targets => [
     *      search => replace,
     *      search1 => replace1,
     *  ]
     * ]
     *
     * @param FileTransformsInterface $T
     * @param $blockSubject
     * @param $nodeAction
     * @param $nodeContents
     * @return bool
     */
    public function executeBlockNode (FileTransformsInterface $T, $blockSubject, $nodeAction, $nodeContents) {
        if ($nodeAction==="add_assets") {
            Transforms::transform()
                ->setLayout($T->getLayout())
                ->addAssets($blockSubject, $nodeContents);
            return true;

        } else if ($nodeAction==="remove_assets") {
            Transforms::transform()
                ->setLayout($T->getLayout())
                ->removeAssets($blockSubject, $nodeContents);
            return true;

        } else if ($nodeAction==="replace_assets") {
            Transforms::transform()
                ->setLayout($T->getLayout())
                ->replaceAssets($blockSubject, $nodeContents);
            return true;

        }
        return false;
    }
}
