<?php

namespace C\FS;

use Moust\Silex\Cache\CacheInterface;

/**
 * Class Registry
 * Walks through and dump content
 * of a file system directory.
 * It has a base path,
 * and can have multiple path alias.
 *
 * It can dump their content into an array such
 * [
 *  file path => [
 *      (meta data)
 *  ]
 * ]
 *
 * It is useful to
 * avoid file systems calls
 * reduce a number of computation
 * keep reliable filemtime information across multiple machines.
 *
 * @package C\FS
 */
class Registry {

    /**
     * the store name used to into the key for caching.
     *
     * @var string
     */
    public $storeName;
    /**
     * The cache store object.
     *
     * @var CacheInterface
     */
    public $cache;
    /**
     * The overall signature of the dumped contents.
     * @var string
     */
    public $signature;

    /**
     * @var array
     */
    public $config = [
        'basePath' => '/', // must always be an absolute path
        'paths' => [],
        'alias' => [],
    ];
    /**
     * dumped content.
     *
     * @var array
     */
    public $items = [
        'relative/file/path'=>[
            'type'          =>'file',
            'name'          =>'abc.php',
            'dir'           =>'some/relative/path/to/file',
            'path'          =>'/absolute/base/path',
            'sha1'          =>'123abc',
            'file_mtime'    =>123,
        ],
        'relative/dir/path'=>[
            'type'          =>'dir',
            'name'          =>'abc',
            'dir'           =>'some/relative/path/to/dir',
            'path'          =>'/absolute/base/path',
            'sha1'          =>'',
            'file_mtime'    =>123,
        ]
    ];

    /**
     * @param $storeName
     * @param CacheInterface $cache
     * @param array $config
     */
    public function __construct ($storeName, CacheInterface $cache, $config=[]) {
        $this->config = array_merge($this->config, $config);
        $this->items = [];
        $this->storeName = $storeName;
        $this->cache = $cache;
        foreach($this->config['paths'] as $i => $path) {
            $this->config['paths'][$i] = cleanPath($path);
        }
    }

    /**
     * Register a path with an alias.
     * So later you can call the file prefixed
     * by its alias instead of its absolute path.
     *
     * @param $path
     * @param null $as string
     * @return mixed
     */
    public function registerPath($path, $as=null){
        $this->config['paths'][] = rp($path);
        if($as) $this->config['alias']["$as:"] = rp($path);
        return $path;
    }

    /**
     * The bae path for relative files path.
     * @param $path string
     * @return string
     */
    public function setBasePath($path){
        $this->config['basePath'] = $path;
        return $path;
    }

    /**
     * @return string
     */
    public function getBasePath(){
        return $this->config['basePath'];
    }

    /**
     * Given a path, return the alias.
     *
     * @param $path string
     * @return bool|string
     */
    public function getAliasFromPath ($path) {
        foreach ($this->config['alias'] as $a=>$p) {
            if ($p===$path) return $a;
        }
        return false;
    }

    /**
     * Tells if the registry is fresh.
     *
     * After a change on the registry, the signature will change,
     * the registry will be stall.
     *
     * @return bool
     */
    public function isFresh(){
        return $this->signature && $this->signature===$this->sign();
    }

    /**
     * Generate and save the signature of the registry.
     *
     * @return $this
     */
    public function createSignature(){
        $this->signature = $this->sign();
        return $this;
    }

    /**
     * sign the registry.
     * if $some is provided,
     * only items found into it are used.
     *
     * @param null $some array
     * @return string
     */
    public function sign($some=null){
        $signature = '';
        $this->each(function ($item, $localPath) use(&$signature, $some) {
            if (!$some || in_array($localPath, $some) || in_array($item['absolute_path'], $some) ) {
                $signature = sha1($signature.$item['sha1']);
            }
        });
        return $signature;
    }

    /**
     * dump and save the resgistry to cache.
     *
     * @return array
     */
    public function saveToCache(){
        $dump = $this->dump();
        $this->cache->store("{$this->storeName}dump", ($dump));
        return $dump;
    }
    public function clearCached(){
        $this->cache->delete('dump');
    }

    /**
     * load the registry from the cache.
     *
     * @return bool
     */
    public function loadFromCache(){
        $dump = $this->cache->fetch("{$this->storeName}dump");
        if ($dump) return $this->loadFromDump(($dump));
        return false;
    }

    /**
     * Given a dump, an array such
     * [
     *  config=> []
     *  items=> []
     *  signature=> []
     * ]
     * rebuild the registry.
     *
     * @param $dump
     * @return bool
     */
    public function loadFromDump($dump){
        $this->config = $dump['config'];
        $this->items = $dump['items'];
        $this->signature = $dump['signature'];
        return true;
    }

    /**
     * recursive browse paths and sign the registry.
     *
     * @return $this
     */
    public function build(){
        return $this->recursiveReadPath()->createSignature();
    }

    /**
     * Dump the content of this registry in an array.
     *
     * @return array
     */
    public function dump(){
        return [
            'items'=>$this->items,
            'config'=>[
                'basePath' => $this->config['basePath'],
                'paths' => $this->getUniversalPath($this->config['paths']),
                'alias' => $this->getUniversalPath($this->config['alias']),
            ],
            'signature'=>$this->signature,
        ];
    }

    /**
     * Returns universal version of a path.
     * Universal version of a path, ideally, is a path relative to basePath.
     * But sometimes, because of links or junctions, they must be absolute-d.
     *
     * @param array $paths
     * @return array
     */
    protected function getUniversalPath (array $paths) {
        $basePath = $this->config['basePath'];
        $ret = [];
        foreach( $paths as $index=>$path) {
            $rp = new \SplFileInfo($path);

            if ($rp->isLink()) {
                $ret[$index] = $rp->getLinkTarget();

            } else if ($rp->isDir()) {
                $rp = $rp->getRealPath();
                if (substr($rp, 0, strlen($basePath))===$basePath) {
                    $rp = substr($rp, strlen($basePath)+1);
                }
                $ret[$index] = $rp;
            }
        }
        $ret = array_unique($ret);
        if (count($ret)!==count($paths)) {
            // mh, something weird like duplicated path.
        }
        return $ret;
    }

    /**
     * Recursively read path contents
     * and save their items.
     *
     * @return $this
     */
    protected function recursiveReadPath () {
        $paths = $this->getUniversalPath($this->config['paths']);
        $this->items = [];
        foreach( $paths as $path) {
            $Directory = new \RecursiveDirectoryIterator($path);
            $filter = new \RecursiveCallbackFilterIterator($Directory, function ($current, $key, $iterator) {
                if (in_array($current->getFilename(), ['..'])) {
                    return false;
                }
                return $current;
            });
            $Iterator = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);

            foreach( $Iterator as $Iterated ) {
                /* @var $Iterated \SplFileInfo */
                $this->addItem($Iterated);
            }
        }
        return $this;
    }

    /**
     * Helper method to add a class as a path.
     *
     * @param $className string
     * @param bool $onlyIfNew
     */
    public function addClassFile ($className, $onlyIfNew=true) {
        $reflector = new \ReflectionClass($className);
        $path = $reflector->getFileName();
        if ($onlyIfNew && !$this->get($path) || !$onlyIfNew) {
            $this->registerPath(dirname($path));
            $this->addItem($path);
        }
    }

    /**
     * dump a file item or a directory into an array of metadata.
     *
     * You ll find in there a number of computed information such
     * type, name, isRelative, dir, sha1, extension, file_mtime, file_ctime.
     *
     * @param $path string
     */
    public function addItem ($path) {
        $basePath = $this->config['basePath'];

        if (is_string($path)) {
            $path = new \SplFileInfo($path);
        }
        /* @var $path \SplFileInfo */
        $fp = $path->getRealPath();
        $isRelative = false;
        if (substr($fp, 0, strlen($basePath))===$basePath) {
            $isRelative = true;
            $fp = substr($fp, strlen($basePath)+1);
        }
        $p = dirname($fp)."".DIRECTORY_SEPARATOR;
        $item = [
            'type'          => $path->isFile()?'file':'dir',
            'name'          => $path->getFilename()==='.'?basename($fp):$path->getFilename(),
            'isRelative'    => $isRelative,
            'dir'           => $p,
            'sha1'          => $path->isFile()?sha1($fp.LocalFs::file_get_contents($fp)):'',
            'extension'     => $path->getExtension(),
            'file_mtime'    => $path->getMTime(),
            'file_atime'    => $path->getATime(),
            'file_ctime'    => $path->getCTime(),
        ];

        $key = $fp.($path->isFile()?'':DIRECTORY_SEPARATOR);
        $this->items[$key] = $item;
    }

    /**
     * @param $path string
     */
    public function removeItem ($path) {
        $basePath = $this->config['basePath'];
        if (is_string($path)) {
            $path = new \SplFileInfo($path);
        }
        $fp = substr($path->getRealPath(), strlen($basePath)+1);
        $key = $fp.($path->isFile()?'':DIRECTORY_SEPARATOR);
        unset($this->items[$key]);
    }

    /**
     * Force an item to re compute.
     * if the item is not already in the registry, it is added.
     *
     * @param $path
     */
    public function refreshItem ($path) {
        $this->addItem($path);
    }

    /**
     * Build and return the absolute path of a file.
     *
     * @param $item
     * @return mixed
     */
    protected function absolutePath ($item) {
        $basePath = $this->config['basePath'];
        $item['absolute_path'] = $item['isRelative']
            ? "$basePath".DIRECTORY_SEPARATOR.$item['dir'].$item['name']
            : $item['dir'].$item['name'];
        return $item;
    }

    /**
     * Transforms a path to an absolute path.
     * It can be relative to the CWD, or a module name (Module:/path/file.ext)
     *
     * @param $itemPath
     * @return bool|string
     */
    public function get($itemPath){
        if (!$itemPath) return false;

        $basePath = $this->config['basePath'];

        $aliasPos = strpos($itemPath, ":");
        $alias = substr($itemPath, 0, $aliasPos+1);

        if ($aliasPos>2 && array_key_exists($alias, $this->config['alias'])) {
            $itemPath = str_replace($alias, $this->config['alias'][$alias], $itemPath);
        }

        if (isset($this->items[$itemPath])) {
            $item = $this->items[$itemPath];
            return $this->absolutePath($item);
        }

        $itemPath = cleanPath($itemPath);

        if (isset($this->items[$itemPath])) {
            $item = $this->items[$itemPath];
            return $this->absolutePath($item);
        }

        if (isset($this->items["$itemPath".DIRECTORY_SEPARATOR])) {
            $item = $this->items["$itemPath".DIRECTORY_SEPARATOR];
            return $this->absolutePath($item);
        }

        if (substr($itemPath, 0, strlen($basePath))===$basePath) {
            $p = substr($itemPath, strlen($basePath)+1);
            if (isset($this->items[$p])) {
                $item = $this->items[$p];
                return $this->absolutePath($item);
            }
            if (isset($this->items["$p".DIRECTORY_SEPARATOR])) {
                $item = $this->items["$p".DIRECTORY_SEPARATOR];
                return $this->absolutePath($item);
            }
        }
        foreach( $this->config['paths'] as $i=>$path) {
            $p = substr($itemPath, 0, strlen($path));
            if (in_array($p, $this->config['paths'])) {
                $itemP = substr($itemPath, strlen($basePath)+1);
                if (isset($this->items[$itemP])) {
                    $item = $this->items[$itemP];
                    return $this->absolutePath($item);
                }
            }
        }
        foreach( $this->config['paths'] as $i=>$path) {
            $p = rp("$basePath".DIRECTORY_SEPARATOR."$itemPath");
            $itemP = substr($p, strlen($basePath)+1);
            if (isset($this->items[$itemP])) {
                $item = $this->items[$itemP];
                return $this->absolutePath($item);
            }
        }
        return false;
    }

    /**
     * iterator helper.
     * @param $callback
     */
    public function each ($callback) {
        foreach($this->items as $i=>$item) {
            $callback($this->absolutePath($item), $i);
        }
    }

    /**
     * Helper method to know if a given path
     * belongs to one of the registered paths.
     *
     * @param $file
     * @return bool
     */
    public function isInRegisteredPaths ($file) {
        $paths = $this->config['paths'];
        $d = realpath(dirname($file));
        foreach ($paths as $path) {
            if (realpath($path)===$d) {
                return true;
            }
        }
        return false;
    }

}

/**
 * make a path string reliable where [.] and [..] are resolved.
 *
 * @param $path
 * @return string
 */
function rp($path) {
    $out=array();
    foreach(explode(DIRECTORY_SEPARATOR, $path) as $i=>$fold){
        if ($fold=='' || $fold=='.') continue;
        if ($fold=='..' && $i>0 && end($out)!='..') array_pop($out);
        else $out[]= $fold;
    } return ($path{0}==DIRECTORY_SEPARATOR?DIRECTORY_SEPARATOR:'').join(DIRECTORY_SEPARATOR, $out);
}

/**
 * Make a path value clean where double-d
 * slashes and backslashes and single-d.
 *
 * @param $path
 * @return mixed
 */
function cleanPath($path) {
    $path = str_replace("//", "/", $path);
    $path = str_replace("\\\\", "\\", $path);
    $path = str_replace("/", DIRECTORY_SEPARATOR, $path);
    $path = str_replace("\\", DIRECTORY_SEPARATOR, $path);
    return $path;
}
