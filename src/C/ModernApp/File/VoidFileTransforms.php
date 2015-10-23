<?php
namespace C\ModernApp\File;

use C\Layout\Transforms\VoidTransforms;

/**
 * Class VoidFileTransforms
 * helps to provide a fluent programming interface
 * by transparently cancel actions run onto it.
 *
 * @package C\ModernApp\File
 */
class VoidFileTransforms extends VoidTransforms implements FileTransformsInterface{

    public function forFacets ($options) {
        if (call_user_func_array([$this->layout->requestMatcher, 'isFacets'],
            func_get_args())) {
            return $this->innerTransform;
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function executeMetaNode ($nodeAction, $nodeContents) {
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

    /**
     * @inheritdoc
     */
    public function importFile($file) {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function then($fn) {
        return $this;
    }
}
