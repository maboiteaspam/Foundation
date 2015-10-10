<?php

namespace C\Assets;

use C\FS\LocalFs;
use C\FS\KnownFs;

class Bridger {

    public function generate ($file, $type, KnownFs $fs) {
        $basePath = $fs->registry->config['basePath'];
        $paths = array_unique($fs->registry->config['paths']);
        $aliases = [];
        if ($type==='builtin') {
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
        } else if ($type==='apache') {
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
        } else if ($type==='nginx') {
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
        }
        return LocalFs::file_put_contents($file, $aliases);
    }

}
