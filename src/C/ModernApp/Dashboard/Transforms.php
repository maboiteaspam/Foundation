<?php
namespace C\ModernApp\Dashboard;

use C\Layout\Transforms\Transforms as Base;
use C\Misc\ArrayHelpers;

/**
 * Class Transforms
 * the layout to inject the dashboard
 * and its extensions into the final HTML
 *
 * @package C\ModernApp\Dashboard
 */
class Transforms extends Base{

    /**
     * @return Transforms
     */
    public static function transform(){
        return new self();
    }

    public function __construct () {
        $this->extensions = new ArrayHelpers();
    }

    /**
     * @var ArrayHelpers
     */
    public $extensions;

    /**
     * Set extensions object available for display in the dashboard.
     * An extension can be any kind object.
     *
     * @param $extensions
     * @return $this
     */
    public function setExtensions ($extensions) {
        $this->extensions->merge($extensions);
        return $this;
    }

    /**
     * Transform the layout in order to show the dashboard.
     * It will display extensions provided in $showExtensions
     * in order.
     *
     * @param string $fromClass
     * @param array $showExtensions
     * @return $this
     */
    public function show ($fromClass=__CLASS__, $showExtensions=[]){
        $this->insertBeforeBlock('footer', 'dashboard', [
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
