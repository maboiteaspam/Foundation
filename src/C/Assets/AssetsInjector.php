<?php

namespace C\Assets;

use C\FS\KnownFs;
use C\FS\LocalFs;
use C\Layout\Layout;
use C\Misc\Utils;
use vierbergenlars\SemVer\version;
use vierbergenlars\SemVer\expression;


/**
 * Class AssetsInjector
 * Walks through layout's blocks
 * - resolves assets requirements against referenced assets on the layout
 * - generate HTML block containing
 *   script and link nodes
 * - it can also generates merged files
 *  given each asset layout's block
 *  template_head_css page_head_css template_head_js page_head_js
 *  template_footer_css page_footer_css template_footer_js page_footer_js
 * - it will also generates inline scripts/css
 *  HTML content for each block
 *
 * @package C\Assets
 */
// @todo study split into sperate class gienve the concerns.
class AssetsInjector {

    /**
     * @var string
     */
    public $buildDir;

    /**
     * @var string
     */
    public $wwwDir;
    /**
     * @var KnownFS
     */
    public $assetsFS;
    /**
     * @var bool
     */
    public $concatenate;

    /**
     * given block id, tells if it s a script / css
     *
     * @param $target
     * @return string
     */
    public function getExtFromTarget ($target){
        return strpos($target, 'js')===false?"css":"js";
    }

    #region apply file assets on target asset blocks
    /**
     * walks through blocks and generate
     * an array of all assets detected.
     *
     * @param Layout $layout
     * @return array
     */
    public function mergeAllAssets (Layout $layout) {
        $blockAssets = [];
        foreach ($layout->getBlocks() as $block) {
            /* @var $block \C\Layout\Block */
            foreach ($block->getAssets() as $target=>$assets) {
                if (!isset($blockAssets[$target])) {
                    $blockAssets[$target] = [];
                }
                $blockAssets[$target] = array_merge($blockAssets[$target], $assets);
            }
        }
        return $blockAssets;
    }
    /**
     * walks through blocks and generate
     * an array of all first assets detected.
     *
     * @param Layout $layout
     * @return array
     */
    public function mergeAllFirstAssets (Layout $layout) {
        $firstAssets = [];
        foreach ($layout->getBlocks() as $block) {
            /* @var $block \C\Layout\Block */
            foreach ($block->getFirstAssets() as $target=>$assets) {
                $firstAssets = array_merge($firstAssets, is_string($assets)?[$assets]:$assets);
            }
        }
        return $firstAssets;
    }

    /**
     * create HTML representation of per file scripts/css
     *
     * @param $target
     * @param $assets
     * @return string
     */
    public function createBridgedHTMLAssets ($target, $assets) {
        $html = '';
        $assetsFS = $this->assetsFS;
        $ext = $this->getExtFromTarget($target);
        foreach ($assets as $asset) {
            $a = $assetsFS->get($asset);
            if ($a) {
                // this is a trick for linux file system
                // get more information at here https://bugs.php.net/bug.php?id=46260
                // as the file is not relative to the project path,
                // it is injected as a virtual path
                //      module:/path/file.ext
                // into the html asset.
                // Later the bridge can do its job.
                // in production it would not be advised to link your vendors modules.
                if ($a['isRelative']) {
                    $assetName = $a['dir'].$a['name'];
                } else {
                    $assetName = $asset;
                }
                $assetUrl = "$assetName?t=".$a['sha1'];

                if ($ext==="js")
                    $html .= sprintf(
                        '<script src="/%s" type="text/javascript"></script>',
                        str_replace("\\", "/", $assetUrl));
                else
                    $html .= sprintf(
                        '<link href="/%s" rel="stylesheet" />',
                        str_replace("\\", "/", $assetUrl));

                $html .= "\n";
            } else {
                $html .= sprintf('<!-- File asset not found %s -->', $asset);
                $html .= "\n";
            }
        }
        return $html;
    }

    /**
     * create HTML representation of concatenate assets
     * given each layout's blocks
     *  template_head_css page_head_css template_head_js page_head_js
     *  template_footer_css page_footer_css template_footer_js page_footer_js
     * it generates a file.
     *
     * @param $target
     * @param $assets
     * @return string
     */
    public function createMergedHTMLAssets ($target, $assets) {
        $html = '';
        $ext = $this->getExtFromTarget($target);
        $assetsFS = $this->assetsFS;
        $buildDir = $this->buildDir;
        $wwwDir = $this->wwwDir;
        if (!LocalFs::is_dir($buildDir)) LocalFs::mkdir($buildDir);
        $basePath = $assetsFS->getBasePath();
        $h = '';
        foreach ($assets as $i=>$asset) {
            if ($assetsFS->file_exists($asset)) {
                $a = $assetsFS->get($asset);
                $h .= $i . '-' . $a['sha1'] . '-';
            }
        }

        // for dev purpose of this file, add this to force refresh on change.
//        if ($layout->debugEnabled)
//            $h = sha1($h.Utils::fileToEtag(__FILE__));
        $h = sha1($h);

        $concatAssetName = "$target-$h.$ext";

        $this->blockToFile[$target] = "$basePath/$buildDir/$concatAssetName";

        $concatAssetUrl = "$wwwDir/$concatAssetName";

        if ($ext==="js")
            $html .= sprintf(
                '<script src="%s" type="text/javascript"></script>',
                str_replace("\\", "/", $concatAssetUrl));
        else
            $html .= sprintf(
                '<link href="%s" rel="stylesheet" />',
                str_replace("\\", "/", $concatAssetUrl));

        return $html;
    }
    public $blockToFile = [];

    /**
     * Appropriately parse, transform and injects
     * assets as files.
     * if concatenate is true,
     *      then it will merge assets of each block into one file.
     * otherwise, foreach asset
     *      it translate into appropriate js / css tag
     *      with the expected path.
     *
     * @param Layout $layout
     */
    public function applyFileAssets (Layout $layout) {
        $allAssets = $this->mergeAllAssets($layout);
        $firstAssets = $this->mergeAllFirstAssets($layout);

        foreach( $allAssets as $target => $assets) {
            $targetBlock = $layout->getOrCreate($target);

            $targetBlock->body .= "\n";

            $assets = array_unique($assets);
            foreach ($firstAssets as $firstAsset) {
                $item = Utils::arrayRemove($assets, $firstAsset);
                if (count($item)) array_unshift($assets, $item[0]);
            }
            if ($this->concatenate===false) {
                $targetBlock->body .= $this->createBridgedHTMLAssets($target, $assets);
            } else {
                $targetBlock->body .= $this->createMergedHTMLAssets($target, $assets);
            }
        }
    }

    /**
     * this method create appropriate merged file
     * given blocks and their assets
     *
     * @param Layout $layout
     */
    public function createMergedAssetsFiles (Layout $layout) {
        $blockToFile = $this->blockToFile;
        $blockAssets = $this->mergeAllAssets($layout);
        foreach ($blockAssets as $target => $assets) {
            if (!LocalFs::file_exists($blockToFile[$target])) {
                $filesContent = [];
                foreach ($assets as $asset) {
                    $filesContent[$asset] = $this->readAndMakeAsset($asset);
                }
                if (strpos($target, 'js')!==false) $c = join(";\n", $filesContent) . ";\n";
                else $c = join("\n", $filesContent) . "\n";
                LocalFs::file_put_contents($blockToFile[$target], $c);
            }
        }
    }

    /**
     * Responsible to transform
     * the content of a script / css file
     *
     * If the file is merged, a css for example,
     * it lost it s ability to load file relative to its path.
     * (it has changed to the merged file).
     * So this function should rewrite the content appropriately
     * to host the content under a different url.
     *
     * Given a JS it will simply inject the script into a function with a
     * modulePath variable.
     *
     * @param $assetFile
     * @return mixed|string
     */
    public function readAndMakeAsset ($assetFile){
        $assetsFS = $this->assetsFS;
        $assetItem  = $assetsFS->get($assetFile);
        if ($assetItem) {
            $assetPath  = $assetItem['absolute_path'];
            $content    = LocalFs::file_get_contents($assetPath);
            $assetShortPath  = $assetItem['dir'].$assetItem['name'];
            if ($assetItem['extension']==='css') {
                $matches = [];
                preg_match_all('/url\s*\(([^)]+)\)/i', $content, $matches);
                foreach($matches[1] as $i=>$match){
                    if (substr($match,0,1)==='"' || substr($match,0,1)==="'") {
                        $match = substr($match, 1, -1);
                    }
                    $content = str_replace($matches[0][$i], "url(/".str_replace("\\", "/", $assetItem['dir'])."/$match)", $content);
                }
                $content = "/* $assetFile -> $assetShortPath */ \n$content";
            } else if ($assetItem['extension']==='js') {
                $content = "(function(modulePath){;".$content.";})('".str_replace("\\", "/", $assetItem['dir'])."');";
            }
        } else {
            $content = "\n/* assset not found $assetFile */\n";
        }

        return $content;
    }
    #endregion


    #region apply inline assets on target asset blocks
    /**
     * Appropriately parse, transform and injects
     * assets as inline contents.
     * @param Layout $layout
     */
    public function applyInlineAssets (Layout $layout) {
        $isAjax = $layout->getRequestMatcher()->isRequestKind('ajax');
        foreach ($layout->registry->blocks as $block) {
            /* @var $block \C\Layout\Block */
            $blockId = $block->id;
            foreach ($block->getInline() as $target=>$inline_items) {
                foreach ($inline_items as $inline) {
                    $content = $inline['content'];
                    $type = $inline['type'];
                    if (!$isAjax ) {
                        $targetBlock = $layout->getOrCreate("{$target}_inline_{$type}");
                    } else {
                        $targetBlock = $layout->getOrCreate("$blockId");
                    }
                    $targetBlock->body .= "\n<!-- {$blockId} -->\n";
                    $targetBlock->body .= "\n{$content}\n";
                }
            }
        }
    }
    #endregion

    #region apply assets requirements
    /**
     * Walks through each block,
     * gets and resolved its requires
     * against layout referenced assets
     *
     * if the asset requirement is successfully
     * satisfied, then the asset path is injected in the given
     * target asset block defined by the
     * latest register_asset rule attached
     * to layout.
     *
     * if the asset can t be satisfied,
     * a meta information can be found into block meta
     *      requires_satisfaction
     *
     * It returns an array about all unique requirements
     * with extra information about their satisfaction
     * and block id requires.
     *
     * @param Layout $layout
     * @return array
     */
    public function applyAssetsRequirements (Layout $layout) {
        $requirementsSatisfaction = [];

        $layoutAssets = $layout->getReferencedAssets();
        if(!count($layoutAssets)) return $requirementsSatisfaction;

        foreach ($layout->getBlocks() as $block) {
            /* @var $block \C\Layout\Block */
            foreach ($block->requires as $requirement) {
                if (!isset($requirementsSatisfaction[$requirement])) {
                    $require    = explode(':', $requirement);
                    $alias      = $require[0];
                    $version    = $require[1];
                    $satisfiedAsset = $this->satisfyAssetRequire($layoutAssets, $alias, $version);
                    $requirementsSatisfaction[$requirement] = [
                        'alias'         => $alias,
                        'version'       => $version,
                        'satisfaction'  => $satisfiedAsset,
                        'on_behalf_of'  => [],
                    ];
                }
                $requirementsSatisfaction[$requirement]['on_behalf_of'] = [$block->id];
            }
        }
        // @todo rewrite this to use the returned array of satisfaction,
        // @todo later inject it into the dashboard to make this data valuable
//        if ($layout->debugEnabled) {
//            foreach ($layout->registry->blocks as $block) {
//                $block->meta['requires_satisfaction'] = [];
//                foreach ($block->requires as $require) {
//                    $block->meta['requires_satisfaction'][$require] =
//                        $requirementsSatisfaction[$require]['satisfaction'];
//                }
//            }
//        }
        foreach ($requirementsSatisfaction as $requirement) {
            if ($requirement['satisfaction']!==false) {
                // @see Layout::registerAsset
                $target     = $requirement['satisfaction'][3];
                $path       = $requirement['satisfaction'][1];
                $first       = $requirement['satisfaction'][4];
                $block_id   = $requirement['on_behalf_of'][count($requirement['on_behalf_of'])-1];
                if (is_string($path)) $path = [$path];
                $layout->get($block_id)->addAssets([$target=>$path], $first);
            }
        }


        return $requirementsSatisfaction;
    }

    /**
     * Given an alias and a desired version
     * walks through each layout referenced asset
     * and tries to satisfy the requirement
     *
     * on success, it returns an array
     * about the asset reference such
     * [
     *  path: Module:/file/path.ext
     *  version: 1.1.1
     *  alias: alias-asset-vendor
     *  target: template_head_js
     * ]
     *
     * @param array $layoutAssets
     * @param $alias
     * @param $desiredVersion
     * @return false|array the referenced asset information
     */
    protected function satisfyAssetRequire ($layoutAssets, $alias, $desiredVersion) {
        foreach ($layoutAssets as $availableAsset) {
            $availableAlias = $availableAsset[0];
            if ($availableAlias===$alias) {
                $availableVersion   = $availableAsset[2];
                $wellKnownRequires  = $availableAsset[5];
                if (in_array($desiredVersion, $wellKnownRequires)) {
                    return $availableAsset;
                }else {
                    $semver     = new version($availableVersion);
                    $satisfy    = $semver->satisfies(new expression($desiredVersion));
                    if ($satisfy) {
                        return $availableAsset;
                    }
                }
            }
        }
        return false;
    }
    #endregion
}
