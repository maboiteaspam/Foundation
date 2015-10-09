<?php
namespace C\ModernApp\File\Helpers;

use C\Layout\Transforms;
use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\File\FileTransformsInterface;

class AssetsHelper extends AbstractStaticLayoutHelper{

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

        }
        return false;
    }
}
