<?php
namespace C\ModernApp\File;

use C\FS\KnownFs;
use C\FS\LocalFs;
use Moust\Silex\Cache\CacheInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Store
 * Knows how to store and fetch a layout from the cache system.
 *
 *
 * @package C\ModernApp\File
 */
class Store {

    /**
     * @var KnownFs
     */
    protected $modernFS;

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
    public function setModernLayoutFS (KnownFs $fs) {
        $this->modernFS = $fs;
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
        $layoutFile     = $this->getFileMeta($filePath);
        try{
            $layoutStruct   = Yaml::parse (LocalFs::file_get_contents ($layoutFile['absolute_path']), true, false, true);
        }catch(\Exception $ex) {
            throw new \Exception("Failed to parse file ".$layoutFile['absolute_path'], 0, $ex);
        }
        $this->cache->store($layoutFile['dir'].'/'.$layoutFile['name'], $layoutStruct);
        return $layoutStruct;
    }

    /**
     * Remove an item from the cache, a layout file.
     * @param $filePath
     * @return bool
     * @throws \Exception
     */
    public function removeFile ($filePath) {
        $layoutFile = $this->getFileMeta($filePath);
        return $this->cache->delete($layoutFile['dir'].'/'.$layoutFile['name']);
    }

    /**
     * @return bool
     */
    public function clearCache () {
        return $this->cache->clear();
    }

    /**
     * Convenience method to get meta data of a layout file.
     *
     * @param $filePath
     * @return bool|string
     * @throws \Exception
     */
    public function getFileMeta ($filePath) {
        $layoutFile = $this->modernFS->get($filePath);
        if( $layoutFile===false) {
            throw new \Exception("File not found $filePath");
        }
        return $layoutFile;
    }

    /**
     * Given a virtual path,
     * fetch the layout content from the cache.
     * Add it to the cache if it does not exists.
     *
     * @param $filePath
     * @return array|mixed
     * @throws \Exception
     */
    public function get ($filePath) {
        $layoutFile     = $this->getFileMeta($filePath);
        $layoutStruct   = $this->cache->fetch($layoutFile['dir'].'/'.$layoutFile['name']);
        if (!$layoutStruct) {
            $layoutStruct = $this->storeFile($filePath);
        }
        return $layoutStruct;
    }
}
