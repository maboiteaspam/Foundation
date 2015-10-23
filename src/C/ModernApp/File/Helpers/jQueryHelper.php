<?php
namespace C\ModernApp\File\Helpers;

use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\File\FileTransformsInterface;
use C\ModernApp\jQuery\Transforms as jQuery;

class jQueryHelper extends  AbstractStaticLayoutHelper{

    /**
     * Provide a new block action to inject jquery in your view.
     *
     * @param FileTransformsInterface $T
     * @param $blockTarget
     * @param $nodeAction
     * @param $nodeContents
     * @return bool
     */
    public function executeBlockNode (FileTransformsInterface $T, $blockTarget, $nodeAction, $nodeContents) {
        if ($nodeAction==="inject_jquery") {
            if (is_string($nodeContents)) {
                $nodeContents = [
                    'version'   => $nodeContents,
                    'target'    => "page_footer_js",
                ];
            }
            $version = $nodeContents['version'];
            $nodeContents = array_merge([
                'jquery'    => "jQuery:/jquery-{$version}.min.js",
                'target'    => $nodeContents['target'],
            ], $nodeContents);
            jQuery::transform()
                ->setLayout($T->getLayout())
                ->inject($nodeContents);
            return true;

        } else if ($nodeAction==="dom_prepend_to") {

        } else if ($nodeAction==="dom_append_to") {

        } else if ($nodeAction==="dom_prepend_with") {

        } else if ($nodeAction==="dom_append_with") {

        } else if ($nodeAction==="dom_remove") {

        }
    }
}
