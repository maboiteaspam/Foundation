<?php
namespace C\HTTP\Cache;

use C\TagableResource\TagedResource;
use Moust\Silex\Cache\CacheInterface;

/**
 * Class Store
 * is responsible to know how to
 * store / fetch an http cached resource.
 *
 * An HTTP cached resource needs a specific Store object
 * as it will record it s data into various keys.
 *
 * - Resource
 *      A serialized TagResource object. It can be used to know if a cache entry is stall or fresh.
 * - Data
 *      A serialized string, usually HTML, containing the actual response of the cached HTTP request.
 * - Url
 *      A raw url value referring it s etag. It is sued to do reverse identification of a cached item from an url, as opposite to using an etag value.
 *
 *
 * @package C\HTTP\Cache
 */
class Store{

    /**
     * @var CacheInterface
     */
    public $cache;
    /**
     * @var string
     */
    public $storeName;

    /**
     * @param $storeName string
     * @param CacheInterface $cache
     */
    public function __construct ($storeName, CacheInterface $cache) {
        $this->cache    = $cache;
        $this->storeName = $storeName;
    }

    /**
     * Store a tagged resource into the cache.
     *
     * @param TagedResource $resource
     * @param $url
     * @param $content
     */
    public function store (TagedResource $resource, $url, $content) {
        $surl = sha1($url);
        $etag = $resource->originalTag;
        $this->cache->store("{$this->storeName}resource-{$etag}.php",   $resource);
        $this->cache->store("{$this->storeName}content-{$etag}.php",    $content);
        $this->cache->store("{$this->storeName}url-{$surl}.php",        $etag);
    }

    /**
     * Given an url, returns the etag computed for it.
     *
     * @param $url
     * @return mixed
     */
    public function getEtag ($url) {
        $surl   = sha1($url);
        $f      = "{$this->storeName}url-{$surl}.php";
        return $this->cache->fetch($f);
    }

    /**
     * Given an etag, returns the resource tag associated with it.
     *
     * @param $etag
     * @return mixed
     */
    public function getResource ($etag) {
        $f = "{$this->storeName}resource-{$etag}.php";
        return $this->cache->fetch($f);
    }

    /**
     * Given an etag, returns the content associated with it.
     *
     * @param $etag
     * @return mixed
     */
    public function getContent ($etag) {
        $f = "{$this->storeName}content-{$etag}.php";
        return ($this->cache->fetch($f));
    }
}