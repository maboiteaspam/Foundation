<?php
namespace C\FS;

use Moust\Silex\Cache\CacheInterface;
use Symfony\Component\Yaml\Yaml;

class Store {

    /**
     * @var KnownFs
     */
    protected $fs;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * FS object responsible to translate
     * virtual path into real file system path.
     *
     * @param KnownFs $fs
     */
    public function setFS (KnownFs $fs) {
        $this->fs = $fs;
    }

    /**
     * The cache object.
     *
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache) {
        $this->cache = $cache;
    }

    /**
     * Store a YML file path into the cache,
     * returns the resulting array of its parsing.
     *
     * @param $filePath
     * @return array
     * @throws \Exception
     */
    public function storeFile ($filePath) {
        $item     = $this->getFileMeta($filePath);
        try{
            $content   = Yaml::parse (LocalFs::file_get_contents ($item['absolute_path']), true, false, true);
        }catch(\Exception $ex) {
            throw new \Exception("Failed to parse file ".$item['absolute_path'], 0, $ex);
        }
        $this->cache->store($item['dir'].'/'.$item['name'], $content);
        return $content;
    }

    /**
     * Remove an item from the cache, a file.
     * @param $filePath
     * @return bool
     * @throws \Exception
     */
    public function removeFile ($filePath) {
        $item = $this->getFileMeta($filePath);
        return $this->cache->delete($item['dir'].'/'.$item['name']);
    }

    /**
     * @return bool
     */
    public function clearCache () {
        return $this->cache->clear();
    }

    /**
     * Convenience method to get meta data of a file.
     *
     * @param $filePath
     * @return bool|string
     * @throws \Exception
     */
    public function getFileMeta ($filePath) {
        $item = $this->fs->get($filePath);
        if( $item===false) {
            throw new \Exception("File not found $filePath");
        }
        return $item;
    }

    /**
     * Given a virtual path,
     * fetch the item content from the cache.
     * Add it to the cache if it does not exists.
     *
     * @param $filePath
     * @return array|mixed
     * @throws \Exception
     */
    public function get ($filePath) {
        $item   = $this->getFileMeta($filePath);
        $item   = $this->cache->fetch($item['dir'].'/'.$item['name']);
        if (!$item) {
            $item = $this->storeFile($filePath);
        }
        return $item;
    }
}