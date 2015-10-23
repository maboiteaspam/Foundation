<?php
namespace C\Layout\Transforms;

use C\Layout\Layout;

/**
 * Class Transforms
 * helps to deal with layout transformation.
 *
 * Layout transformation is about
 * declaring and configuring block structuring the layout object.
 *
 * @package C\Layout\Transforms
 */
class Transforms implements TransformsInterface{

    /**
     * @return Transforms
     */
    public static function transform(){
        return new self();
    }

    /**
     * @var \C\Layout\Layout
     */
    public $layout;

    /**
     * @param Layout $layout
     * @return $this
     */
    public function setLayout (Layout $layout) {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @return Layout
     */
    public function getLayout () {
        return $this->layout;
    }

    /**
     * @param mixed $some
     * @return $this
     */
    public function then($some=null) {
        if (is_callable($some)) $some($this);
        return $this;
    }

    /**
     * Set options of a Block
     *
     * @param $id
     * @param $options
     * @return $this
     */
    public function set($id, $options){
        $this->layout->set($id, $options);
        return $this;
    }

    /**
     * Set template file of a block.
     *
     * @param $id
     * @param $template
     * @return $this
     */
    public function setTemplate($id, $template){
        $block = $this->layout->getOrCreate($id);
        if ($block) {
            $block->setTemplate($template);
        }
        return $this;
    }

    /**
     * Clear options of a block.
     *
     * $what can be one of
     * - all
     * - template
     * - data
     * - options
     * - assets
     * - meta
     *
     * You can also pass in a string such
     * template, options
     * to clear multiple elements at once.
     *
     * @param $id
     * @param string $what
     * @return $this
     */
    public function clearBlock($id, $what='all'){
        $block = $this->layout->getOrCreate($id);
        if ($block) {
            $block->clear($what);
        }
        return $this;
    }

    /**
     * Deletes and remove a block from the layout,
     * cancelling it s processing.
     *
     * @param $id
     * @return $this
     */
    public function deleteBlock($id){
        $this->layout->remove($id);
        return $this;
    }

    /**
     * Sets the body content of a block.
     *
     * When you do so, the block won t execute any template file.
     *
     * @param $id
     * @param $body
     * @return $this
     */
    public function setBody($id, $body){
        $block = $this->layout->getOrCreate($id);
        if ($block) {
            $block->clear();
            $block->body = $body;
        }
        return $this;
    }

    /**
     * Exclude a block from resource tagging.
     *
     * It is useful for blocks such dashboard
     * which are part of the developer toolbox.
     *
     * @param $id
     * @return $this
     */
    public function excludeFromTagResource($id){
        $block = $this->layout->getOrCreate($id);
        if ($block) {
            $block->options['tagresource_excluded'] = true;
        }
        return $this;
    }

    /**
     * Update options of a block with an array_merge.
     *
     * @param $id
     * @param array $options
     * @return $this
     */
    public function updateOptions($id, $options=[]){
        $block = $this->layout->getOrCreate($id);
        $block->options = array_merge($options, $block->options);
        return $this;
    }

    /**
     * Attach an assets to the given block id for rendering.
     *
     * @param $id
     * @param array $assets
     * @param bool $first
     * @return $this
     */
    public function addAssets($id, $assets=[], $first=false){
        $block = $this->layout->getOrCreate($id);
        $block->addAssets($assets, $first);
        return $this;
    }

    /**
     * Remove an asset of the given block id.
     *
     * @param $id
     * @param array $assets
     * @return $this
     */
    public function removeAssets($id, $assets=[]){
        $block = $this->layout->getOrCreate($id);
        foreach($assets as $targetAssetGroupName => $files) {
            if(!isset($block->assets[$targetAssetGroupName]))
                $block->assets[$targetAssetGroupName] = [];
            foreach($files as $file) {
                $index = array_search($file, $block->assets[$targetAssetGroupName]);
                if ($index!==false) {
                    array_splice($files, $index, 1);
                }
            }
        }
        return $this;
    }

    /**
     * Performs a strict search on the given block id assets
     * in order to replace it in place with the new given asset.
     *
     * $replacements is an array such
     * [
     *  search => replacement,
     *  search2 => replacement2,
     *  search3 => replacement3,
     * ]
     *
     *
     * @param $id
     * @param array $replacements
     * @return $this
     */
    public function replaceAssets($id, $replacements=[]){
        $block = $this->layout->getOrCreate($id);
        foreach($replacements as $search => $replacement) {
            foreach ($block->assets as $blockAssetsName=>$blockAssets) {
                foreach($blockAssets as $i=>$asset) {
                    if ($asset===$search) {
                        $block->assets[$blockAssetsName][$i] = $replacement;
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Sets default data of a block.
     * It won t override existing data.
     *
     * @param $id
     * @param array $data
     * @return $this
     */
    public function setDefaultData($id, $data=[]){
        $block = $this->layout->getOrCreate($id);
        $block->data = array_merge($data, $block->data);
        return $this;
    }

    /**
     * Sets default meta of a block.
     * It won t override existing meta.
     *
     * @param $id
     * @param array $meta
     * @return $this
     */
    public function setDefaultMeta($id, $meta=[]){
        $block = $this->layout->getOrCreate($id);
        $block->meta = array_merge($meta, $block->meta);
        return $this;
    }

    /**
     * Update data of the given block.
     * $data will override block data.
     *
     * @param $id
     * @param array $data
     * @return $this
     */
    public function updateData($id, $data=[]){
        $block = $this->layout->getOrCreate($id);
        $block->data = array_merge($block->data, $data);
        return $this;
    }

    /**
     * @deprecated
     * @todo remove it.
     */
    public function addIntl($id, $intl, $locale, $domain=null){
        $block = $this->layout->getOrCreate($id);
        $block->intl[] = [
            'item'=>$intl,
            'locale'=>$locale,
            'domain'=>$domain,
        ];
        return $this;
    }
    /**
     * @deprecated
     * @todo remove it.
     */
    public function replaceIntl($search, $replace){
        foreach ($this->layout->registry->blocks as $i=>$block) {
            foreach ($block->intl as $e=>$intl) {
                if ($intl['item']===$search) {
                    $this->layout->registry->blocks[$i]->intl[$e]['item'] = $replace;
                }
            }
        }
        return $this;
    }
    /**
     * @deprecated
     * @todo remove it.
     */
    public function removeIntl($search){
        foreach ($this->layout->registry->blocks as $i=>$block) {
            foreach ($block->intl as $e=>$intl) {
                if ($intl['item']===$search) {
                    unset($this->layout->registry->blocks[$i]->intl[$e]);
                }
            }
        }
        return $this;
    }

    /**
     * Update meta of the given block.
     * $meta will override block meta.
     *
     * @param $id
     * @param array $meta
     * @return $this
     */
    public function updateMeta($id, $meta=[]){
        $block = $this->layout->getOrCreate($id);
        $block->meta = array_merge($block->meta, $meta);
        return $this;
    }

    /**
     * helper method to block
     * option, meta, data in one call.
     *
     * @param $id
     * @param array $meta
     * @param array $data
     * @param array $options
     * @return $this
     */
    public function updateBlock($id, $meta=[], $data=[], $options=[]){
        $block = $this->layout->getOrCreate($id);
        $block->meta = array_merge($block->meta, $meta);
        $block->data = array_merge($block->data, $data);
        $block->options = array_merge($block->options, $options);
        return $this;
    }

    /**
     * This method erases all block
     * which id does not match given pattern
     *
     * @deprecated
     * @todo remove it
     *
     * @param $pattern
     * @return $this
     */
    public function keepOnly($pattern){
        $this->layout->keepOnly($pattern);
        return $this;
    }


    /**
     * switch to a device type
     * desktop, mobile, tablet
     * default is desktop
     *
     * When the current request device
     * does not match the expected $device,
     * a void transform is provided.
     *
     * @param $device
     * @return $this|VoidTransforms
     */
    public function forDevice ($device) {
        if (call_user_func_array([$this->layout->requestMatcher, 'isDevice'],
            func_get_args())) {
            return $this;
        }
        $T = new VoidTransforms();
        return $T
            ->setLayout($this->getLayout())
            ->setInnerTransform($this);
    }

    /**
     * switch to a request kind
     * ajax, get
     * default is get
     * esi-slave, esi-master are esi internals.
     * it can also receive negate kind such
     * !ajax !esi-master !esi-slave !get
     *
     * @param $kind
     * @return $this|VoidTransforms
     */
    public function forRequest ($kind) {
        if (call_user_func_array([$this->layout->requestMatcher, 'isRequestKind'], func_get_args())) {
            return $this;
        }
        $T = new VoidTransforms();
        return $T
            ->setLayout($this->getLayout())
            ->setInnerTransform($this);
    }
    public function forLang ($lang) {
        if (call_user_func_array([$this->layout->requestMatcher, 'isLang'], func_get_args())) {
            return $this;
        }
        $T = new VoidTransforms();
        return $T
            ->setLayout($this->getLayout())
            ->setInnerTransform($this);
    }

    /**
     * Insert given block $id after $target.
     *
     * @param $target
     * @param $id
     * @param array $options
     * @return $this
     */
    public function insertAfterBlock ($target, $id, $options=[]){
        $this->layout->set($id, $options);
        $this->layout->afterBlockResolve($target, function ($ev, Layout $layout) use($target, $id) {
            $layout->resolve($id);
        });
        $this->layout->afterBlockRender($target, function ($ev, Layout $layout) use($target, $id) {
            $block = $layout->registry->get($target);
            if ($block) {
                $block->body = $block->body.$layout->getContent($id);
            } else {
                // @todo issue warning
            }
            $parent = $layout->registry->getParent($target);
            if ($parent) {
                $parent->registerDisplayedBlockAfter($target, $id, true);
                $block = $layout->registry->get($id);
                if ($block) {
                    $block->setParentRenderBlock($parent->id);
                }
            } else {
                // @todo issue warning
            }
        });
        return $this;
    }

    /**
     * Insert given block $id before $target.
     *
     * @param $beforeTarget
     * @param $id
     * @param array $options
     * @return $this
     */
    public function insertBeforeBlock ($beforeTarget, $id, $options=[]){
        $this->layout->set($id, $options);
        $this->layout->beforeBlockResolve($beforeTarget, function ($ev, Layout $layout) use($beforeTarget, $id) {
            $layout->resolve($id);
        });
        $this->layout->afterBlockRender($beforeTarget, function ($ev, Layout $layout) use($beforeTarget, $id) {
            $block = $layout->registry->get($beforeTarget);
            if ($block) {
                $block->body = $layout->getContent($id).$block->body;
            }
            $parent = $layout->registry->getParent($beforeTarget);
            if ($parent) {
                $parent->registerDisplayedBlockAfter($beforeTarget, $id, true);
                $block = $layout->registry->get($id);
                if ($block) {
                    $block->setParentRenderBlock($parent->id);
                }
            }
        });
        return $this;
    }
}
