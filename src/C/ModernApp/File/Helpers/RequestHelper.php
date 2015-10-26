<?php
namespace C\ModernApp\File\Helpers;

use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\File\FileTransformsInterface;

/**
 * Class RequestHelper
 * provides facility to execute given action
 * for a specific kind of request such ajax / esi ect
 *
 * @package C\ModernApp\File\Helpers
 */
class RequestHelper extends  AbstractStaticLayoutHelper{

    //@todo add ua
    //@todo add http accept
    //@todo add request locale
    /**
     * Provides three new structure actions
     *  for_device: <device type>
     *  for_lang: <lang desired>
     *  for_facets:
     *      device: <device type>
     *      lang: <lang desired>
     *      request: <request kind>
     *
     * @param FileTransformsInterface $T
     * @param $nodeAction
     * @param $nodeContents
     * @return mixed
     */
    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContents) {
        if (substr($nodeAction, 0, strlen("for_device_"))==="for_device_") {
            $device = substr($nodeAction, strlen("for_device_"));
            return $T->forDevice($device);

        } else if (substr($nodeAction, 0, strlen("for_lang_"))==="for_lang_") {
            $lang = substr($nodeAction, strlen("for_lang_"));
            return $T->forLang($lang);


        } else if ($nodeAction==="for_facets") {
            return $T->forFacets($nodeContents);

        }
    }
}
