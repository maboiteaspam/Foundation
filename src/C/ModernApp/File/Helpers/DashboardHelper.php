<?php
namespace C\ModernApp\File\Helpers;

use C\Layout\Transforms;
use C\ModernApp\File\AbstractStaticLayoutHelper;
use C\ModernApp\Dashboard\Transforms as Dashboard;
use C\ModernApp\File\FileTransformsInterface;

class DashboardHelper extends  AbstractStaticLayoutHelper{

    /**
     * @var array
     */
    public $extensions = [];

    public function setExtensions ($extensions) {
        $this->extensions = array_merge($this->extensions, $extensions);
        return $this;
    }

    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContents) {
        if ($nodeAction==="show_dashboard") {
            if ($T->getLayout()->debugEnabled) {
                Dashboard::transform()
                    ->setLayout($T->getLayout())
                    ->setExtensions($this->extensions)
                    ->show(__CLASS__, $nodeContents);
            }
            return true;
        }
    }

    public function executeBlockNode (FileTransformsInterface $T, $subject, $nodeAction, $nodeContents) {
        if ($nodeAction==="debug_with") {
            if ($T->getLayout()->debugEnabled) {
                Transforms::transform()
                    ->setLayout($T->getLayout())
                    ->setDefaultMeta($subject, ['debug_with' => $nodeContents]);
            }
            return true;
        }
    }
}
