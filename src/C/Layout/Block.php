<?php
namespace C\Layout;

use C\TagableResource\TagedResource;
use C\TagableResource\TagableResourceInterface;
use C\TagableResource\UnwrapableResourceInterface;
use C\View\Context;
use C\FS\KnownFs;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class Block
 * represents a render-able element of a layout.
 * It has a template, options, data(s), meta, and can be attached assets.
 * It is being executed to render a portion of the whole page.
 * It can declare and render sub blocks.
 *
 *
 * @package C\Layout
 */
class Block implements TagableResourceInterface{

    public $id;
    public $body;
    public $parentId;
    public $resolved = false;

    public $options = [
    ];
    public $data = [];
    public $assets = [];
    public $inline = [];
    public $intl = [];
    public $meta = [
        'from' => false,
        'etag' => '',
    ];

    // this are runtime data to help debug and so on.
    public $stack = [];
    public $displayed_blocks = [
        /* [array,of,block,id,displayed]*/
    ];


    /**
     * @param $id string
     */
    public function __construct($id) {
        $this->id = $id;
    }

    /**
     * clear some settings of the block.
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
     * @param string $what
     */
    public function clear ($what='all') {
        if ($what==='all' || $what==='') {
            $this->body = "";
            $this->data = [];
            $this->assets = [];
            $this->options = [
                'template' => ''
            ];
        } else {
            if (strpos($what, "template")!==false) {
                $this->options['template'] = '';
            }
            if (strpos($what, "data")!==false) {
                $this->data = [];
            }
            if (strpos($what, "options")!==false) {
                $this->options = ["template" => ""];
            }
            if (strpos($what, "assets")!==false) {
                $this->assets = [];
            }
            if (strpos($what, "meta")!==false) {
                $this->meta = [];
            }
        }
    }

    /**
     * Resolves the view within the block settings.
     *
     * @param KnownFs $fs
     * @param Context $context
     */
    public function resolve (KnownFs $fs, Context $context){
        if (!$this->resolved) {
            $this->resolved = true;
            if (isset($this->options['template'])
                && $this->options['template']) {

                $fn = $this->options['template'];
                if(!is_callable($this->options['template'])) {
                    $fn = function (Block $block) use($fs) {
                        ob_start();
                        extract($block->unwrapData(['block']), EXTR_SKIP);
                        $template = $fs->get($block->options['template']);
                        if ($template!==false) require ($template['absolute_path']);
                        else require ($block->options['template']);
                        $block->body = ob_get_clean();
                    };
                }

                if ($fn) {
                    $context->setBlockToRender($this);
                    $boundFn = \Closure::bind($fn, $context);
                    try{
                        $boundFn($this);
                    }catch(\Exception $ex) {
                        throw new Exception("'{$this->id}' has failed to execute: {$ex->getMessage()}", 0, $ex);
                    }
                } else {
                    // weird stuff in template.
                }
            }
        }
    }

    /**
     * $template can be a file path
     * or a module file target (My/Module:/path/file.ext)
     *
     * @param $template string
     */
    public function setTemplate($template){
        $this->options['template'] = $template;
    }
    public function getTemplate(){
        return $this->options['template'];
    }

    /**
     * @param $parentId string
     */
    public function setParentRenderBlock($parentId){
        $this->parentId = $parentId;
    }

    /**
     * @return string
     */
    public function getParentBlockId(){
        return $this->parentId;
    }

    /**
     * adds a block content of a script/css inline.
     * $target is first/head/foot/last
     * @param $target
     * @param $type
     * @param $content
     */
    public function addInline($target, $type, $content){
        if (!isset($this->inline[$target]))
            $this->inline[$target] = [];
        $this->inline[$target][] = [
            'type'=>$type,
            'content'=>$content,
        ];
    }

    /**
     * @return array
     */
    public function getInline(){
        return $this->inline;
    }

    /**
     * Attach a new asset to this block.
     * $assets is an array such
     * [
     *  'target'=>[
     *      assets1.css,
     *      assets2.jpeg,
     *  ]
     * ]
     *
     * target is a block id relate to your base template.
     * It is probably something of
     * - template_head_css
     * - page_head_css
     * - template_head_js
     * - page_head_js
     * ----
     * - template_footer_css
     * - page_footer_css
     * - template_footer_js
     * - page_footer_js
     *
     * @param array $assets
     * @param bool|false $first
     */
    public function addAssets($assets=[], $first=false){
        foreach($assets as $targetAssetGroupName => $files) {
            if(!isset($this->assets[$targetAssetGroupName]))
                $this->assets[$targetAssetGroupName] = [];
            $this->assets[$targetAssetGroupName] = $first
                ? array_merge($files, $this->assets[$targetAssetGroupName])
                : array_merge($this->assets[$targetAssetGroupName], $files);
        }
    }

    /**
     * Compute involved resources of the block
     * as a resource tag.
     *
     * @return TagedResource
     * @throws \Exception
     */
    public function getTaggedResource (){
        $res = new TagedResource();

        if ($this->resolved) {
            $res->addResource($this->id);
            if (isset($this->options['template'])) {
                $template = $this->options['template'];
                if ($template) {
                    $res->addResource($template, 'template');
                }
            }
            foreach($this->assets as $target=>$assets) {
                foreach($assets as $i=>$asset){
                    if ($asset) {
                        $res->addResource($target);
                        $res->addResource($i);
                        $res->addResource($asset, 'asset');
                    }
                }
            }
            foreach($this->intl as $i=>$intl) {
                $res->addResource($i);
                $res->addResource($intl, 'intl');
            }

            foreach($this->data as $name => $data){
                if ($data instanceof TagableResourceInterface) {
                    $res->addTaggedResource($data->getTaggedResource(), $name);
                } else {
                    $res->addResource($data, 'po', $name);
                }
            }
        }

        return $res;
    }

    /**
     * Get all data attached to this block unwrapped.
     * @param array $notNames
     * @return array
     * @throws \Exception
     */
    public function unwrapData ($notNames=[]) {
        $unwrapped = [];
        foreach($this->data as $name => $data){
            if (!in_array($name, $notNames, true)) {
                $unwrapped[$name] = $this->getData($name);
            } else {
                throw new \Exception("Forbidden data name '$name' is forbidden and can t be overwritten");
            }
        }
        return $unwrapped;
    }

    /**
     * Get an unwrapped data attached to this block.
     *
     * @param $name
     * @return mixed
     */
    public function getData ($name) {
        $data = $this->data[$name];
        if ($data instanceof UnwrapableResourceInterface) {
            $data = $data->unwrap();
        }
        return $data;
    }

    /**
     * Returns the list of blocks the view has tried to display.
     *
     * @return array
     */
    public function getDisplayedBlocksId () {
        $displayed = [];
        foreach ($this->displayed_blocks as $d) {
            $displayed[] = $d['id'];
        }
        return $displayed;
    }

    /**
     * Register the id of a block the view has displayed.
     *
     * @param $id
     * @param bool $shown
     */
    public function registerDisplayedBlock($id, $shown=true) {
        $this->displayed_blocks[] = ["id"=>$id, "shown"=>$shown];
    }

    /**
     * Update the list of displayed block to register a new id after $afterId.
     *
     * @param $afterId
     * @param $id
     * @param bool $shown
     */
    public function registerDisplayedBlockAfter($afterId, $id, $shown=true) {
        $index = array_keys($this->getDisplayedBlocksId(), $afterId);
        if (count($index)) {
            $index = $index[0];
            array_splice($this->displayed_blocks, $index, 0, [["id"=>$id, "shown"=>$shown]]);
        } else {
            $this->displayed_blocks[] = ["id"=>$id, "shown"=>$shown];
        }
    }

    /**
     * Update the list of displayed block to register a new id before $beforeId.
     *
     * @param $beforeId
     * @param $id
     * @param bool $shown
     */
    public function registerDisplayedBlockBefore($beforeId, $id, $shown=true) {
        $index = array_keys($this->getDisplayedBlocksId(), $beforeId);
        if (count($index)) {
            $index = $index[0];
            array_splice($this->displayed_blocks, $index, 0, [["id"=>$id, "shown"=>$shown]]);
        } else {
            array_unshift($this->displayed_blocks, ["id"=>$id, "shown"=>$shown]);
        }
    }
}
