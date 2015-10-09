<?php
namespace C\ModernApp\DashboardExtension;

use C\Layout\Transforms as Base;
use C\Layout\Layout;
use C\Misc\Utils;

class Transforms extends Base{

    /**
     * @return Transforms
     */
    public static function transform(){
        return new self();
    }

    /**
     * @return \C\Layout\Transforms
     */
    public function time_travel (){
        $this->setTemplate('dashboard-time-travel',
            'DashboardExtension:/time-travel.php'
        )->addAssets('dashboard-time-travel', [
            'template_head_css'=>[
            ],
            'page_footer_js'=>[
            ],
        ])->insertAfterBlock("dashboard-body", "dashboard-time-travel");

        return $this;
    }

    /**
     * @return \C\Layout\Transforms
     */
    public function stats (){

        $this->set('dashboard-stats', [
            'options' => [
                'template'=>'DashboardExtension:/stats.php'
            ],
            'data' => [
                'options'=> []
            ]
        ])->addAssets('dashboard-stats', [
        ])->insertAfterBlock("dashboard-body", "dashboard-stats");

        return $this;
    }

    /**
     * @param string $fromClass
     * @return $this
     */
    public function structure_visualizer ($fromClass=__CLASS__){
        if ($this->layout->serializer) {
            $serializer = $this->layout->serializer;

            $this->set('dashboard-structure-pholder', [
                'body' => "<!-- layout_structure_placeholder -->",
            ])->addAssets('dashboard-structure-pholder', [
                'template_head_css'=>[
                    'DashboardExtension:/layout-structure.css'
                ],
                'page_footer_js'=>[
                    'DashboardExtension:/layout-structure.js'
                ],
            ])->insertAfterBlock("dashboard-body",
                "dashboard-structure-pholder");

            // this is a special case.
            // the block needs to be generated after ALL blocks,
            // then re injected into the document.
            $this->layout->afterRender(function ($ev, Layout $layout) use($serializer) {
                $rootBlock = $layout->getRoot();

                if ($rootBlock) {
                    $content = $rootBlock->body;

                    $this->set('dashboard-structure', [
                        'options' => [
                            'template'=>'DashboardExtension:/layout-structure.php'
                        ],
                        'data' => [
                            'serialized'=> $serializer->serialize($layout)
                        ]
                    ])->excludeFromTagResource('dashboard-structure');

                    $rootBlock->body = str_replace(
                        "<!-- layout_structure_placeholder -->",
                        $layout->resolve('dashboard-structure')->body,
                        $content);
                }
            });


            $this->layout->beforeRenderAnyBlock(function ($ev, Layout $layout, $id) use($fromClass) {
                $block = $layout->get($id);
                if ($block) {
                    $caller = Utils::findCaller($block->stack, $fromClass);
                    $caller = \json_encode($caller);
                    $debug_with = isset($block->meta['debug_with'])
                        ?$block->meta['debug_with']
                        :"node";
                    if ($debug_with==="node") {
                        $block->body = "\n<c_block_node id='$id' caller='$caller'>\n".$block->body;
                    } else {
                        $block->body = "\n<!-- START id='$id' caller='$caller'  -->\n".$block->body;
                    }
                }
            });
            $this->layout->afterRenderAnyBlock(function ($ev, Layout $layout, $id) {
                $block = $layout->get($id);
                if ($block) {

                    $debug_with = isset($block->meta['debug_with'])
                        ?$block->meta['debug_with']
                        :"node";
                    if ($debug_with==="node") {
                        $block->body = $block->body."\n</c_block_node>\n";
                    } else {
                        $block->body = $block->body."\n<!-- END id='$id' -->\n";
                    }
                    $block->body = trim($block->body)."\n";
                }
            });

        }
        return $this;
    }

}
