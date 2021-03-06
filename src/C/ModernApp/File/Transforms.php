<?php
namespace C\ModernApp\File;

use C\Layout\Transforms\Transforms as BaseTransforms;
use C\Layout\Transforms\TransformsInterface;
use C\Misc\Utils;
use C\TagableResource\TagedResource;
use C\FS\Store;

/**
 * Class Transforms
 * to update layout instance given
 * an input provided by a file path
 *
 *
 * @package C\ModernApp\File
 */
class Transforms extends BaseTransforms implements FileTransformsInterface{

    #region setter ect
    /**
     * @return Transforms
     */
    public static function transform(){
        return new self();
    }

    /**
     * @var Store
     */
    protected $store;

    /**
     * Helpers are responsible to declare and implement
     * actions to apply on layout, structure, or blocks.
     * such as
     *  import:
     *  add_assets:
     *  [select block id]
     *
     * They can receive dynamic values.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * @param StaticLayoutHelperInterface $helper
     * @return $this
     */
    public function addHelper (StaticLayoutHelperInterface $helper) {
        $this->helpers[] = $helper;
        return $this;
    }

    /**
     * @param $helpers array
     * @return $this
     */
    public function setHelpers($helpers) {
        $this->helpers = $helpers;
        return $this;
    }

    /**
     * Store is the object responsible
     * to resolve layout file path.
     *
     * @param Store $store
     * @return $this
     */
    public function setStore(Store $store) {
        $this->store = $store;
        return $this;
    }
    #endregion

    #region transform switcher
    /**
     * Takes an array of constraints and
     * require them to return true.
     *
     * [
     *  device => !desktop
     *  lang=>fr
     * ]
     *
     * @param $facets
     * @return $this|VoidFileTransforms
     */
    public function forFacets ($facets) {
        if (call_user_func_array([$this->layout->requestMatcher, 'isFacets'],
            func_get_args())) {
            return $this;
        }
        $T = new VoidFileTransforms();
        return $T
            ->setLayout($this->getLayout())
            ->setInnerTransform($this);
    }
    /**
     * Switch the current transform depending on the device type attached to the request being processed.
     * $device can be any, mobile, desktop, tablet
     *
     * When the device mismatch, a void transform is returned.
     *
     * default is desktop
     *
     * @param $device
     * @return $this|VoidFileTransforms
     */
    public function forDevice ($device) {
        if (call_user_func_array([$this->layout->requestMatcher, 'isDevice'],
            func_get_args())) {
            return $this;
        }
        $T = new VoidFileTransforms();
        return $T
            ->setLayout($this->getLayout())
            ->setInnerTransform($this);
    }
    /**
     * Switch the current transform depending on the kind of request being processed.
     * $kind can be any, get, esi-slave, esi-master, ajax
     *
     * When the kind of request mismatch, a void transform is returned.
     *
     * default is get
     * esi-slave, esi-master are internals for esi processing.
     *
     * It is possible to use negation such
     * !ajax
     * !esi-master
     * !esi-slave
     * !get
     *
     * @param $kind
     * @return $this|VoidFileTransforms
     */
    public function forRequest ($kind) {
        if (call_user_func_array([$this->layout->requestMatcher, 'isRequestKind'], func_get_args())) {
            return $this;
        }
        $T = new VoidFileTransforms();
        return $T
            ->setLayout($this->getLayout())
            ->setInnerTransform($this);
    }

    /**
     * @param $lang
     * @return $this
     */
    public function forLang ($lang) {
        if (call_user_func_array([$this->layout->requestMatcher, 'isLang'], func_get_args())) {
            return $this;
        }
        $T = new VoidFileTransforms();
        return $T
            ->setLayout($this->getLayout())
            ->setInnerTransform($this);
    }
    #endregion

    /**
     * Imports the given file
     * and process it
     * on the current layout.
     *
     * @param $filePath
     * @return $this
     * @throws \Exception
     */
    public function importFile ($filePath) {
        $layoutStruct = $this->store->get($filePath);

        $resourceTag = new TagedResource();
        $resourceTag->addResource($filePath, 'modern.layout');
        $this->layout->addGlobalResourceTag($resourceTag);

        if (isset($layoutStruct['meta'])) {
            foreach ($layoutStruct['meta'] as $nodeAction=>$nodeContent) {
                if (!$this->executeMetaNode($nodeAction, $nodeContent)) {
                    // mhh
                }
            }
        }

        // is there reason to create a new transform object
        // where $this is already a transform ?
        $structure = Transforms::transform()
            ->setLayout($this->getLayout())
            ->setStore($this->store)
            ->setHelpers($this->helpers);

        // @todo this loop will need good tests
        if (isset($layoutStruct['structure'])) {
            $sub = $structure;
            foreach ($layoutStruct['structure'] as $actions) {
                // loop
                foreach ($actions as $action => $options) {

                    // passToEach
                    $sub = $this->executeStructureNode((is_object($sub)?$sub:$structure), $action, $options);

                    if ($sub instanceof TransformsInterface) {
                        $structure = $sub;
                    } else if($sub===false) {
                        $subject = $action;
                        $nodeActions = $options;
                        $structure->then(function (FileTransformsInterface $T) use($subject, $nodeActions) {
                            foreach ($nodeActions as $nodeAction=>$nodeContent) {
                                // catchAllToEach
                                if (!$this->executeBlockNode($T, $subject, $nodeAction, $nodeContent)) {
                                    // mhh
                                    // dead as not found,
                                    // in fact, it just keep going.
                                    //@todo put a log here, there is keyword which is not evaluated properly.
                                }
                            }
                        });

                    }
                }
            }
        }
        return $this;
    }

    /**
     * Executes a meta node,
     * one that affects layout object properties.
     *
     * @param $nodeAction
     * @param $nodeContent
     * @return bool
     */
    public function executeMetaNode ($nodeAction, $nodeContent) {
        // @todo improve this to avoid the repeated double loop
        // @todo helpers should be identifiable by their token id
        // @todo if isset($this->tokens[$tokenId])
        // @todo  then $this->tokens[$tokenId]->execute(...);
        foreach ($this->helpers as $helper) {
            /* @var $helper StaticLayoutHelperInterface */
            if ($helper->executeMetaNode($this->layout, $nodeAction, $nodeContent)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Execute a structural layout node.
     * For examples,
     * - importing a file
     * - active / void transform switch via facets
     * - dashboard insertion
     * are considered as a structural action
     *
     * @param FileTransformsInterface $T
     * @param $nodeAction
     * @param $nodeContent
     * @return bool|FileTransformsInterface
     */
    public function executeStructureNode (FileTransformsInterface $T, $nodeAction, $nodeContent) {
        foreach ($this->helpers as $helper) {
            /* @var $helper StaticLayoutHelperInterface */
            $sub = $helper->executeStructureNode($T, $nodeAction, $nodeContent);
            if ($sub) return $sub;
        }
        return false;
    }

    /**
     * Executes an action on a block level.
     * Add assets, set template, change data are actions that concerns blocks.
     *
     * @param FileTransformsInterface $T
     * @param $subject
     * @param $nodeAction
     * @param $nodeContent
     * @return bool
     */
    public function executeBlockNode (FileTransformsInterface $T, $subject, $nodeAction, $nodeContent) {
        foreach ($this->helpers as $helper) {
            /* @var $helper StaticLayoutHelperInterface */
            if ($helper->executeBlockNode($T, $subject, $nodeAction, $nodeContent)) {
                return true;
            }
        }
        return false;
    }

}
