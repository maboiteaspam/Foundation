<?php
namespace C\ModernApp\jQuery;

use C\Layout\Transforms\Transforms as base;

/**
 * Class Transforms
 * provide helpers to work with jquery and ajax
 *
 * @package C\ModernApp\jQuery
 */
class Transforms extends base{

    /**
     * @return Transforms
     */
    public static function transform(){
        return new self();
    }

    /**
     * @param array $options
     * @return $this
     * @deprecated prefer using jQuery:/register.yml
     */
    public function inject($options=[]){
        $options = array_merge([
            'jquery' => 'jQuery:/jquery-2.1.3.min.js',
            'target' => 'page_footer_js',
        ], $options);
        $this->addAssets('body', [
            $options['target']=>[$options['jquery']],
        ], true);
        return $this;
    }

    /**
     * @param array $options
     * @return $this
     * @deprecated prefer not using it.
     */
    public function tooltipster($options=[]){
        $options = array_merge([
            'js'        => 'jQuery:/tooltipster-master/js/jquery.tooltipster.min.js',
            'css'       => 'jQuery:/tooltipster-master/css/tooltipster.css',
            'theme'     => 'jQuery:/tooltipster-master/css/themes/tooltipster-shadow.css',
            'css_target'=> 'page_head_css',
            'js_target' => 'page_footer_js',
        ], $options);
        $this->addAssets('body', [
            $options['css_target'] => [$options['css'], $options['theme']],
            $options['js_target'] => [$options['js']],
        ]);
        return $this;
    }

    /**
     * ajaxify given target block id.
     *
     * options is an array of values such
     * [
     *  url => url to ajax render url
     * ]
     *
     *
     * @param $target
     * @param array $options
     * @return $this
     */
    public function ajaxify($target, $options=[]){
        $options = array_merge(['url'=>'',], $options);
        // use layout capabilities to target non ajax queries
        // and injected the js code to trigger the ajax loading
        return $this->forRequest('!ajax')
            ->then(function (Transforms $transform) use ($target, $options) {
                $id = sha1($target.$options['url']);

                $transform->clearBlock($target
                )->setBody($target,
                    '<div id="'.$id.'"></div>'
                )->requireAssets($target, 'jquery:2.x || 1.x'
                )->setTemplate($target.'_ajax',
                    'jQuery:/ajaxified-block.php'
                )->updateData($target.'_ajax', [
                    'url'   => $options['url'],
                    'id'    => $id,
                    'target'=> $target,
                ]);
                $this->insertAfterBlock('page_footer_js', $target.'_ajax', []);
            })
            // use layout capabilities to target ajax queries
            // detect the targeted block, if that matches,
            // set root layout as ajaxified block id to render the block
            // and its children.
            ->forRequest('ajax')
            ->then(function (Transforms $transform) use($target) {
                if ($_GET['target']===$target) {
                    $transform->getLayout()->block = $target;
                }
            });
    }

    // jQuery like methods
    public function prependTo ($selector) {}
    public function appendTo ($selector) {}
    public function insertAfter ($selector) {}
    public function insertBefore ($selector) {}
    public function remove ($selector) {}
    public function addAttr ($selector) {}
    public function removeAttr ($selector) {}
    public function addClass ($selector) {}
    public function removeClass ($selector) {}
}
