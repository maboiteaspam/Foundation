<?php
namespace C\ModernApp\DashboardExtension;

use C\Layout\Transforms\Transforms as Base;
use C\Layout\Layout;

/**
 * Class Transforms
 * the layout to inject dashboard extensions
 *
 * Dashboard extensions
 * are methods of this class, such as
 *
 * time_travel, structure_visualizer ect
 *
 * @package C\ModernApp\DashboardExtension
 */
class Transforms extends Base{

    /**
     * @return Transforms
     */
    public static function transform () {
        return new self();
    }

    /**
     * @var LayoutSerializer
     */
    public $serializer;

    /**
     * @param LayoutSerializer $serializer
     * @return $this
     */
    public function setLayoutSerializer (LayoutSerializer $serializer) {
        $this->serializer = $serializer;
        return $this;
    }

    /**
     * Display a time travel extension to change date of browsing.
     *
     * @return \C\Layout\Transforms\Transforms
     */
    public function time_travel () {
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
     * Displays a stats extension to show information.
     *
     *
     * @return \C\Layout\Transforms\Transforms
     */
    public function stats () {

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
     * Display a tree representation of the layout.
     *
     * @param string $fromClass
     * @return $this
     */
    public function structure_visualizer ($fromClass=__CLASS__) {
        if ($this->serializer) {
            $serializer = $this->serializer;

            $this->set('dashboard-structure-pholder', [
                'body' => "<!-- layout_structure_placeholder -->",
            ])->addAssets('dashboard-structure-pholder', [
                'template_head_css'=>[
                    'DashboardExtension:/layout-structure.css'
                ],
                'page_footer_js'=>[
                    'DashboardExtension:/layout-structure.js'
                ],
            ])->requireAssets(
                'dashboard-structure-pholder', ['jquery:2.x || 1.x']
            )->insertAfterBlock("dashboard-body",
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
                            'template'  => 'DashboardExtension:/layout-structure.php'
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
                    $debug_with = isset($block->meta['debug_with'])
                        ?$block->meta['debug_with']
                        :"node";
                    if ($debug_with==="node" && $block->body) {
                        $block->body = "\n<c_block_node id='$id'>\n".$block->body;
                    } else if($block->body) {
                        $block->body = "\n<!-- START id='$id' -->\n".$block->body;
                    }
                }
            });
            $this->layout->afterRenderAnyBlock(function ($ev, Layout $layout, $id) {
                $block = $layout->get($id);
                if ($block) {

                    $debug_with = isset($block->meta['debug_with'])
                        ?$block->meta['debug_with']
                        :"node";
                    if ($debug_with==="node" && $block->body) {
                        $block->body = $block->body."\n</c_block_node>\n";
                    } else if($block->body) {
                        $block->body = $block->body."\n<!-- END id='$id' -->\n";
                    } else {
                        $block->body = "\n<!-- BLOCK id='$id'  -->\n";
                    }
                    $block->body = trim($block->body)."\n";
                }
            });

        }
        return $this;
    }

}
