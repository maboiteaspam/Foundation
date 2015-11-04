<?php

namespace C\Assets;

use C\Layout\Transforms\Transforms;
use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\File\FileTransformsInterface;

/**
 * Class AssetsLayoutFileHelper
 * Provide new block actions to add / remove / replace assets.
 *
 * It can also register and require assets
 *
 *
 * structure:
 *
 *  - register_assets:
 *      alias: [require alias]
 *      path: Module:/asset-to-add.js
 *      version: [asset version]
 *      target: [target_layout_asset_block]
 *      first: [true|false]
 *
 *  [block_id]:
 *      require: [asset_alias:compatible_version]
 *
 *  [block_id]:
 *      add_assets:
 *          [target_layout_asset_block]:
 *              - Module:/asset-to-add.js
 *      remove_assets:
 *          [target_layout_asset_block]:
 *              - Module:/asset-to-add.js
 *
 *
 *
 * @package C\Assets
 */
class AssetsLayoutFileHelper extends AbstractStaticLayoutHelper{

    /**
     * Provide a new structure action
     * to reference an asset on the layout
     *
     * $nodeContents is an array such [
     *  alias => jquery,
     *  path => /fs/path/to/jquery.js,
     *  version => x.x,
     *  target => page_footer_js,
     *  first => false,
     * ]
     *
     * @param FileTransformsInterface $T
     * @param $nodeAction
     * @param $nodeContents
     * @return bool
     */
    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContents) {
        if ($nodeAction==="register_assets") {
            Transforms::transform()
                ->setLayout($T->getLayout())
                ->registerAssets(
                    $nodeContents['alias'],
                    $nodeContents['path'],
                    $nodeContents['version'],
                    $nodeContents['target'],
                    isset($nodeContents['first'])?$nodeContents['first']:false,
                    isset($nodeContents['satisfy'])?$nodeContents['satisfy']:[]);
            return true;

        }
        return false;
    }

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

        } else if ($nodeAction==="require") {
            Transforms::transform()
                ->setLayout($T->getLayout())
                ->requireAssets($blockSubject, $nodeContents);
            return true;

        }
        return false;
    }
}
