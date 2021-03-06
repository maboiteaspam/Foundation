<?php
namespace C\ModernApp\File;

/**
 * Class ImportLayoutFileHelper
 * Provides a new structure node
 * to import a layout file
 * from another layout file.
 *
 * structure:
 *  - import_file: Module:/path/to/layout.yml
 *
 * @package C\ModernApp\File
 */
class ImportLayoutFileHelper extends  AbstractStaticLayoutHelper{

    /**
     * Provides a new structure node action
     * to import a layout file.
     *
     * - import_file: Module:/path/to/layout.yml
     *
     * It can also accepts an array
     *
     * - import_file:
     *      - Module:/path/to/layout.yml
     *      - Module:/path/to/layout2.yml
     *
     *
     * @param FileTransformsInterface $T
     * @param $nodeAction
     * @param $nodeContents
     * @return bool
     */
    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContents) {
        if ($nodeAction==="import") {
            if (is_string($nodeContents)) {
                $nodeContents = [$nodeContents];
            }
            foreach ($nodeContents as $n) {
                $T->importFile($n);
            }
            return true;
        }
    }
}
