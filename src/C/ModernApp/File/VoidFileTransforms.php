<?php
namespace C\ModernApp\File;

use C\Layout\Transforms\VoidTransforms;

class VoidFileTransforms extends VoidTransforms implements FileTransformsInterface{

    public function forFacets ($options) {
        if (call_user_func_array([$this->layout->requestMatcher, 'isFacets'],
            func_get_args())) {
            return $this->innerTransform;
        }
        return $this;
    }
    public function executeMetaNode ($nodeAction, $nodeContents) {}

    /**
     * @param FileTransformsInterface $T
     * @param $nodeAction
     * @param $nodeContents
     * @return Transforms
     */
    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContents) {
        return false;
    }

    public function executeBlockNode (FileTransformsInterface $T, $subject, $nodeAction, $nodeContents) {
    }

    public function importFile($file) {
        return $this;
    }

    public function then($fn) {
        return $this;
    }
}
