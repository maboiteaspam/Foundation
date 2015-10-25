<?php

namespace C\Watch;

use C\FS\Store;

class WatchedStore extends WatchedRegistry {

    /**
     * @var Store
     */
    public $store;

    public function setStore (Store $store) {
        $this->store = $store;
    }

    public function clearCache (){
        parent::clearCache();
        $this->store->clearCache();
    }

    public function resolveRuntime () {
        parent::resolveRuntime();
    }

    public function build () {
        parent::build();
        $store = $this->store;
        $this->registry->each(function ($item) use($store) {
            try{
                if ($item['extension']) {
                    $store->storeFile($item['absolute_path']);
                }
            }catch(\Exception $ex) {
                echo $ex->getMessage()."\n";
            }
        });
        return $this;
    }

    public function changed ($action, $file) {
        $updated = false;
        if ($action==='unlink'){
            $item = $this->registry->get($file);
            if ($item) {
                $this->store->removeFile($item['absolute_path']);
                $updated = true;
            }
        }

        parent::changed($action, $file);

        if($action==='change' || $action==='add' || $action==='addDir'){
            $item = $this->registry->get($file);
            if ($item) {
                $this->store->storeFile($item['absolute_path']);
                $updated = true;
            } else if ($this->registry->isInRegisteredPaths($file)) {
                $this->registry->addItem($file);
                $this->registry->saveToCache();
                $item = $this->registry->get($file);
                $this->store->storeFile($item['absolute_path']);
                $updated = true;
            }
        }
        return $updated;
    }

}