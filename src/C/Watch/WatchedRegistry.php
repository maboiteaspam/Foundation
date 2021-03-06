<?php

namespace C\Watch;

use C\FS\Registry;
use C\Misc\Utils;

class WatchedRegistry extends AbstractWatched {

    /**
     * @var Registry
     */
    public $registry;

    public function setRegistry (Registry $registry) {
        $this->registry = $registry;
    }

    public function clearCache (){
        $this->registry->clearCached();
    }

    public function resolveRuntime () {}

    public function build () {
        $this->registry->build();
        return $this;
    }

    public function dump () {
        return $this->registry->dump();
    }

    public function loadFromCache () {
        return $this->registry->loadFromCache();
    }

    public function saveToCache () {
        return $this->registry->saveToCache();
    }

    public function changed ($action, $file) {
        $updated = false;
        if ($action==='unlink'){
            $item = $this->registry->get($file);
            if ($item) {
                $this->registry->removeItem($file);
                $updated = true;
            }
        } else if($action==='change'){
            $item = $this->registry->get($file);
            if ($item) {
                $this->registry->refreshItem($file);
                $updated = true;
            }
        } else if($action==='add' || $action==='addDir'){
            if ($this->registry->isInRegisteredPaths($file)) {
                $this->registry->addItem($file);
                $updated = true;
            }
        }

        if ($updated) {
            $this->registry->createSignature()->saveToCache();
        }

        return $updated;
    }
}