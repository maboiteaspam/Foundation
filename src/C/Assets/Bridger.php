<?php

namespace C\Assets;

use C\FS\LocalFs;
use C\FS\KnownFs;

/**
 * Class Bridger
 * helps to generate a file that bridge
 * internal path system to a real file system.
 *
 * In other words it declares Aliases to inject into your web-server
 * to give access of your resources to the end user.
 *
 * @package C\Assets
 */
class Bridger {

    /**
     * Generate and save content of the bridge.
     *
     * @param $file
     * @param $type
     * @param KnownFs $fs
     * @return int
     */
    public function generate ($file, $type, KnownFs $fs) {

        $aliases = [];

        if ($type==='builtin') {
            $aliases = $this->generateBuiltInBridge($fs);

        // @todo check this part
        } else if ($type==='apache') {
            $aliases = $this->generateApacheBridge($fs);

        // @todo check this part
        } else if ($type==='nginx') {
            $aliases = $this->generateNginxBridge($fs);

        }

        return LocalFs::file_put_contents($file, $aliases);
    }

    /**
     * Generates bridge file content for a built in server.
     *
     * @param KnownFs $fs
     * @return array|string
     */
    public function generateBuiltInBridge(KnownFs $fs) {

        $basePath = $fs->registry->config['basePath'];
        $paths = array_unique($fs->registry->config['paths']);
        $aliases = [];

        foreach ($paths as $i=>$path) {
            if (substr(realpath($path), 0, strlen(realpath($basePath)))===realpath($basePath))
                $urlAlias = substr(realpath($path), strlen(realpath($basePath)));
            else if ($fs->registry->getAliasFromPath($path)!==false){
                $urlAlias = "/".$fs->registry->getAliasFromPath($path);
            }
            $urlAlias = str_replace(DIRECTORY_SEPARATOR, "/", $urlAlias);
            $aliases[$urlAlias] = realpath($path);
        }
        $aliases = "<?php return ".var_export($aliases, true).";\n";

        return $aliases;
    }

    /**
     * Generates bridge file content for an apache server.
     *
     * @param KnownFs $fs
     * @return string
     */
    public function generateApacheBridge(KnownFs $fs) {

        $basePath = $fs->registry->config['basePath'];
        $paths = array_unique($fs->registry->config['paths']);

        // @todo check this part
        $aliases = "";
        foreach ($paths as $path) {
            if (substr(realpath($path), 0, strlen(realpath($basePath)))===realpath($basePath))
                $urlAlias = substr(realpath($path), strlen(realpath($basePath)));
            else if ($fs->registry->getAliasFromPath($path)!==false){
                $urlAlias = "/".$fs->registry->getAliasFromPath($path);
            }
            $urlAlias = str_replace(DIRECTORY_SEPARATOR, "/", $urlAlias);
            $aliases .= "Alias $urlAlias\t$path\n";
        }

        return $aliases;
    }

    /**
     * Generates bridge file content for an nginx server.
     *
     * @param KnownFs $fs
     * @return string
     */
    public function generateNginxBridge(KnownFs $fs) {

        $basePath = $fs->registry->config['basePath'];
        $paths = array_unique($fs->registry->config['paths']);

        // @todo check this part
        $aliases = "";
        foreach ($paths as $path) {
            if (substr(realpath($path), 0, strlen(realpath($basePath)))===realpath($basePath))
                $urlAlias = substr(realpath($path), strlen(realpath($basePath)));
            else if ($fs->registry->getAliasFromPath($path)!==false){
                $urlAlias = "/".$fs->registry->getAliasFromPath($path);
            }
            $urlAlias = str_replace(DIRECTORY_SEPARATOR, "/", $urlAlias);
            $aliases .= "Alias $urlAlias\t$path\n";
        }

        return $aliases;
    }


}
