<?php
namespace C\Layout;

use C\Layout\Layout;
use C\Layout\Transforms\Transforms;
use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\File\FileTransformsInterface;

/**
 * Class BaseLayoutFileHelper
 * Provides the basics transforms to apply on layout.
 *
 * @package C\Layout
 */
class BaseLayoutFileHelper extends  AbstractStaticLayoutHelper{

    /**
     * Provides meta node to set/update id and description of the layout
     *
     * ---
     *
     * meta:
     *  id: LayoutID
     *  description: As you like.
     *
     * @param Layout $layout
     * @param $nodeAction
     * @param $nodeContents
     * @return bool
     */
    public function executeMetaNode (Layout $layout, $nodeAction, $nodeContents) {
        if ($nodeAction==="id") {
            $layout->setId($nodeContents);
            return true;

        } else if ($nodeAction==="description") {
            $layout->setDescription($nodeContents);
            return true;
        }
    }

    /**
     * Provides basic block action node such
     *
     * structure:
     *  block_id:
     *      set_template: Module:/path/to/template.php
     *      body: |
     *          Content of the body as HTML.
     *      set_default_data:
     *          key: value
     *          pair: of data
     *      update_meta:
     *          key: value
     *          pair: of meta
     *      insert_before: target_block_id to insert before
     *      insert_after: target_block_id to insert after
     *      clear: <what> all / options / meta / data
     *      delete: ~
     *
     * @param FileTransformsInterface $T
     * @param $blockSubject
     * @param $nodeAction
     * @param $nodeContents
     * @return bool
     */
    public function executeBlockNode (FileTransformsInterface $T, $blockSubject, $nodeAction, $nodeContents) {
        if ($nodeAction==="set_template") {
            Transforms::transform()
                ->setLayout($T->getLayout())
                ->setTemplate($blockSubject, (string)$nodeContents);
            return true;

        } else if ($nodeAction==="body") {
            Transforms::transform()
                ->setLayout($T->getLayout())
                ->setBody($blockSubject, (string)$nodeContents);
            return true;

        } else if ($nodeAction==="set_default_data") {
            Transforms::transform()
                ->setLayout($T->getLayout())
                ->setDefaultData($blockSubject, $nodeContents);
            return true;

        } else if ($nodeAction==="update_meta") {
            Transforms::transform()
                ->setLayout($T->getLayout())
                ->updateMeta($blockSubject, $nodeContents);
            return true;

        } else if ($nodeAction==="insert_before") {
            if (is_string($nodeContents)) {
                $nodeContents = [
                    'target'    =>$nodeContents,
                    'options'   =>[],
                ];
            }
            $nodeContents = array_merge([
                'target'=>'',
                'options'=>[],
            ],$nodeContents);
            Transforms::transform()
                ->setLayout($T->getLayout())
                ->insertBeforeBlock($nodeContents['target'], $blockSubject, $nodeContents['options']);
            return true;

        } else if ($nodeAction==="insert_after") {
            if (is_string($nodeContents)) {
                $nodeContents = [
                    'target'    =>$nodeContents,
                    'options'   =>[],
                ];
            }
            $nodeContents = array_merge([
                'target'    =>'',
                'options'   =>[],
            ],$nodeContents);
            Transforms::transform()
                ->setLayout($T->getLayout())
                ->insertAfterBlock($nodeContents['target'], $blockSubject, $nodeContents['options']);
            return true;

        } else if ($nodeAction==="clear") {
            Transforms::transform()
                ->setLayout($T->getLayout())
                ->clearBlock($blockSubject, $nodeContents);
            return true;

        } else if ($nodeAction==="delete") {
            Transforms::transform()
                ->setLayout($T->getLayout())
                ->deleteBlock($blockSubject);
            return true;

        }
    }
}
