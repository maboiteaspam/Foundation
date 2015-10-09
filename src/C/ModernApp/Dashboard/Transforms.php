<?php
namespace C\ModernApp\Dashboard;

use C\Layout\Transforms as Base;

class Transforms extends Base{

    /**
     * @return Transforms
     */
    public static function transform(){
        return new self();
    }

    /**
     * @var array
     */
    public $extensions = [];

    public function setExtensions ($extensions) {
        $this->extensions = array_merge($this->extensions, $extensions);
        return $this;
    }

    /**
     * @param string $fromClass
     * @param array $showExtensions
     * @return $this
     */
    public function show ($fromClass=__CLASS__, $showExtensions=[]){
        $this->insertBeforeBlock('html_end', 'dashboard', [
            'options' => [
                'template'=>'Dashboard:/dashboard.php'
            ]
        ])->addAssets('dashboard', [
            'template_head_css'=>[
                'Dashboard:/dashboard.css'
            ],
            'page_footer_js'=>[
                'Dashboard:/dashboard.js'
            ],
        ])->excludeFromTagResource('dashboard');

        foreach ($this->extensions as $extension) {
            foreach ($showExtensions as $showExtension) {
                if (method_exists($extension, $showExtension)) {
                    $extension->{$showExtension}($fromClass);
                }
            }
        }

        return $this;
    }
}
