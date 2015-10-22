<?php
namespace C\Assets;

use C\FS\LocalFs;
use C\FS\KnownFs;
use C\Misc\Utils;

/**
 * Class BuiltinResponder
 * helps to respond to static assets requests
 * for a built in php web server
 *
 * @package C\Assets
 */
class BuiltinResponder {

    /**
     * @var string
     */
    public $wwwDir;

    /**
     * @var \C\FS\KnownFs
     */
    public $fs;

    /**
     * The underlying file system consumed to resolve assets path.
     *
     * @param KnownFs $fs
     */
    public function setFS (KnownFs $fs) {
        $this->fs = $fs;
    }


    /**
     * Forge and respond asset content.
     *
     * @param $f
     * @param $extension
     * @return null|string
     */
    public function sendAsset ($f, $extension) {

        $content = file_get_contents($f);

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $if_modified_since = preg_replace('/;.*$/', '',   $_SERVER['HTTP_IF_MODIFIED_SINCE']);
        } else {
            $if_modified_since = '';
        }
        $mtime = filemtime($f);
        $gmdate_mod = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
        if ($if_modified_since == $gmdate_mod) {
            header("HTTP/1.0 304 Not Modified");
            return null;
        }
        header("Last-Modified: $gmdate_mod");

        if ($extension==="js") {
            header('Content-Type: application/x-javascript; charset=UTF-8');
        } else if ($extension==="css") {
            header('Content-Type: text/css; charset=UTF-8');
        }

        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60*60*24*45)) . ' GMT');

        return $content;
    }

    /**
     * Detects assets request and tries to answer them,
     * does nothing if the request does not look likes an asset,
     * returns 404 on not found asset
     * Can detect module file path (MyModule:/path/file.ext)
     * or regular relative file path to the document root (/regular/path/file.ext).
     *
     * @param bool|false $verbose
     */
    public function respond ($verbose=false) {

        $reqUrl = $_SERVER['PHP_SELF'];
        $acceptableAssets = ['jpeg','jpg','png','gif','css','js'];
        $extension = substr(strrchr($reqUrl, "."), 1);

        // we want to test/try only specific extensions.
        if (in_array($extension, $acceptableAssets)) {

            if (substr($reqUrl,0,1)==='/' && strpos($reqUrl,':')!==false) {
                $reqUrl = substr($reqUrl, 1); // remove the leading slash.
            }

            $item = $this->fs->get($reqUrl); // try to find it into the FS object
            if ($item) {
                echo $this->sendAsset($item['absolute_path'], $item['extension']);
                if ($verbose) Utils::stdout("served $reqUrl");

            } else if (LocalFs::file_exists("{$this->wwwDir}{$reqUrl}")) { // try to consider it as a regular file path relative to document root.
                echo $this->sendAsset("{$this->wwwDir}{$reqUrl}", strpos('js', $reqUrl)===false?'css':'js');
                if ($verbose) Utils::stdout("served $reqUrl");

            } else { // no not found.
                header("HTTP/1.0 404 Not Found");
                if ($verbose) Utils::stdout("missed $reqUrl");
            }
            exit;
        }
        //-
    }
}