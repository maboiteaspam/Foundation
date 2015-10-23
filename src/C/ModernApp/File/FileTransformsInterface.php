<?php
namespace C\ModernApp\File;

use C\Layout\Transforms\TransformsInterface;

/**
 * Interface FileTransformsInterface
 * defines the interface to provide
 * for a layout transforms from a file.
 *
 * @package C\ModernApp\File
 */
interface FileTransformsInterface extends TransformsInterface{

    /**
     * This method aims to execute transforms on the layout property object.
     *
     * @param $file
     * @return mixed
     */
    public function importFile($file);

    /**
     * This aims to execute action on a specified block id.
     *
     * @param $nodeAction
     * @param $nodeContent
     * @return mixed
     */
    public function executeMetaNode ($nodeAction, $nodeContent);

    /**
     * This methods aims to execute transforms on the structure of the layout.
     *
     * @param FileTransformsInterface $T
     * @param $nodeAction
     * @param $nodeContent
     * @return mixed
     */
    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContent);

    /**
     * This aims to execute action on a specified block id.
     *
     * @param FileTransformsInterface $T
     * @param $subject
     * @param $nodeAction
     * @param $nodeContent
     * @return mixed
     */
    public function executeBlockNode (FileTransformsInterface $T, $subject, $nodeAction, $nodeContent);

    #region @todo move it out
    public function then($fn);
    public function forFacets($options);
    public function forDevice($device);
    public function forLang($lang);
    #endregion

}
