<?php

namespace C\Layout\Misc;

use C\FS\KnownFs;
use C\Layout\Block;
use C\Layout\Layout;
use C\Misc\Utils;
use Silex\Application;

/**
 * Class LayoutSerializer
 * is an helper that knows how to serialize a layout.
 * It s main purpose is to display the layout tree
 * into the dashboard structure extension.
 *
 * It s a one way serializer, it does not attempt
 * to provide a structure which is un-serializable to recover
 * the original object.
 *
 *
 * @package C\Layout\Misc
 */
class LayoutSerializer {

    /**
     * @var KnownFS
     */
    public $modernFS;
    /**
     * @var KnownFS
     */
    public $assetsFS;
    /**
     * @var KnownFS
     */
    public $layoutFS;
    /**
     * @var KnownFS
     */
    public $intlFS;
    /**
     * @var Application
     */
    public $app;

    /**
     * layoutFS is used to translate
     * templates virtual path to real path.
     *
     * @param KnownFs $layoutFS
     */
    public function setLayoutFS (KnownFs $layoutFS) {
        $this->layoutFS = $layoutFS;
    }

    /**
     * modernFS is used to translate
     * templates virtual path to real path.
     *
     * @param KnownFs $modernFS
     */
    public function setModernFS (KnownFs $modernFS) {
        $this->modernFS = $modernFS;
    }

    /**
     * assetsFS is used to translate
     * assets virtual path to real path.
     *
     * @param KnownFs $assetsFS
     */
    public function setAssetsFS (KnownFs $assetsFS) {
        $this->assetsFS = $assetsFS;
    }

    /**
     * intlFS is used to translate
     * intl virtual path to real path.
     *
     * @param KnownFs $intlFS
     */
    public function setIntlFS (KnownFs $intlFS) {
        $this->intlFS = $intlFS;
    }

    /**
     * The silex application
     * It is used to discover class names of entity provider.
     *
     * @param Application $app
     */
    public function setApp (Application $app) {
        $this->app = $app;
    }

    /**
     * Serialize transforms the given layout
     * into an array where internal references
     * are translated into real world human information.
     *
     * @param Layout $layout
     * @return array
     */
    public function serialize (Layout $layout) {
        //-
        $serialized = [
            'layout'=>[],
            'blocks'=>[],
        ];

        // @todo add layout meta information (id, description, and injected modern layouts)
        // @todo add intl

        $layoutFS   = $this->layoutFS;
        $assetsFS   = $this->assetsFS;
        $intlFS     = $this->intlFS;
        $modernFS   = $this->modernFS;

        $blocks = [];

        $app = $this->app;
        $root = $layout->get($layout->block);
        $cacheExcludedBlocks = $layout->excludedBlocksFromTagResource();

        $layout->traverseBlocksWithStructure($root, $layout,
            function ($blockId, $parentId, $path, $options)
            use($app, &$blocks, $modernFS, $layoutFS, $assetsFS, $intlFS, $cacheExcludedBlocks) {
                $block = $options['block'];
                /* @var $block Block */
                $template = 'inlined body';
                $templateFile = '';
                $parentId = '';
                $assets = [];
                $data = [];
                $displayedBlocks = [];
                $isCacheExcluded = false;
                $isCacheable = true;

                if ($block) {
                    $parentId = $block->getParentBlockId();
                    $displayedBlocks = $block->getDisplayedBlocksId();
                    $isCacheExcluded = in_array($block->id, $cacheExcludedBlocks);
                    if (isset($block->options['template'])) {
                        $template = $block->options['template'];
                        $templateFile = $layoutFS->get($block->options['template']);
                        $templateFile = Utils::shorten($templateFile['absolute_path']);
                    }
                    if ($this->assetsFS) {
                        foreach ($block->assets as $assetGroup=>$assetsGroup) {
                            if (!isset($assets[$assetGroup])) $assets[$assetGroup] = [];
                            foreach ($assetsGroup as $asset) {
                                $item = $this->assetsFS->get($asset);
                                $assets[$assetGroup][] = [
                                    'name'=>$asset,
                                    'path'=> $item?$item['dir'].$item['name']:'not found'
                                ];
                            }
                        }
                    }

                    $blockTags = null;
                    try{
                        $blockTags = $block->getTaggedResource();
                    }catch(\Exception $ex){

                    }
                    $unWrapped = $block->unwrapData();

                    foreach( $unWrapped as $k=>$v) {
                        $tags = !$blockTags?[]:$blockTags->getResourcesByName($k);
                        $tagsClear = [];
                        foreach ($tags as $tag) {
                            $t = [
                                'type'=>$tag['type'],
                                'value'=>''
                            ];
                            if ($tag['type']==='repository') {
                                $t['value'] = $tag['value'][0]."->".$tag['value'][1][0];
                                $t['type'] = get_class($app[$tag['value'][0]]);
                            } else if ($tag['type']==='asset' || $tag['type']==='modern.layout') {
                                // @todo to complete, check tagDataWith('asset'...
//                            var_dump($tag);
//                            $t['file'] = $tag['value'][0]."->".$tag['value'][1][0];
                            } else if ($tag['type']==='sql') {
                                // @todo to complete, check tagDataWith('sql'...
//                            var_dump($tag);
                            } else if ($tag['type']==='po') {
                                $t['value'] = var_export($tag['value'], true);
                            }
                            $tagsClear[] = $t;
                        }
                        $data[] = [
                            'name' =>$k,
                            'tags' => $tagsClear,
                            'value' => is_object($v)? get_class($v):
                                is_array($v) ? "Array(".gettype($v).")[".count($v)."]" : var_export($v, true)
                            ,
                        ];
                    }

                    try{
                        serialize($unWrapped);
                    }catch(\Exception $ex){
                        $isCacheable = false;
                    }
                }

                $blocks[$path] = [
                    'template'=>$template,
                    'templateFile'=>$templateFile,
                    'assets'=>$assets,
                    'id'=>$blockId,
                    'displayedBlocks'=>$displayedBlocks,
                    'data'=>$data,
                    'exists'=>$options['exists'],
                    'shown'=>$options['shown'],
                    'cacheExcluded'=>$isCacheExcluded,
                    'isCacheable'=>$isCacheable,
                    'parentId'=>$parentId,
                ];
            });

        $serialized['blocks'] = $blocks;

        return $serialized;
    }
}
