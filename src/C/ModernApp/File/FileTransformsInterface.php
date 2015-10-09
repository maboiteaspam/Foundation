<?php
namespace C\ModernApp\File;

interface FileTransformsInterface{
    /**
     * @return \C\Layout\Layout
     */
    public function getLayout();
    public function then($fn);
    public function forFacets($options);
    public function forDevice($device);
    public function forLang($lang);
    public function importFile($file);
    public function executeMetaNode ($nodeAction, $nodeContent);
    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContent);
    public function executeBlockNode (FileTransformsInterface $T, $subject, $nodeAction, $nodeContent);

}
