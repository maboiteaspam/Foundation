<?php
namespace C\ModernApp\File;

use C\Layout\TransformsInterface;

interface FileTransformsInterface extends TransformsInterface{
    public function then($fn);
    public function forFacets($options);
    public function forDevice($device);
    public function forLang($lang);
    public function importFile($file);
    public function executeMetaNode ($nodeAction, $nodeContent);
    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContent);
    public function executeBlockNode (FileTransformsInterface $T, $subject, $nodeAction, $nodeContent);

}
