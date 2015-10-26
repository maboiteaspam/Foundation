<?php

namespace C\Misc;

use C\FS\LocalFs;

/**
 * Class Utils
 * is a fourre-tout class
 * put there some shorthands.
 *
 * @package C\Misc
 */
class Utils {

    #region console output
    //@todo replace this with monolog
    public static $stdoutHandle;
    public static $stderrHandle;
    public static function stderr ($message) {
        fwrite(self::$stderrHandle, "$message\n");
    }

    public static function stdout ($message) {
        fwrite(self::$stdoutHandle, "$message\n");
    }
    #endregion

    public static function fileToEtag ($file) {
        if (is_string($file)) $file = [$file];
        $h = '-';
        foreach ($file as $i=>$f) {
            $h .= $i . '-';
            $h .= $f . '-';
            if (LocalFs::file_exists($f)) {
                $h .= LocalFs::filemtime($f) . '-';
            }
        }
        return $h;
    }

    #region data
    /**
     * An PO to a dictionary array
     * @param $d
     * @return array
     */
    public static function objectToArray($d) {
        if (is_object($d)) {
            $d = get_object_vars($d);
        }
        if (is_array($d)) {
            return array_map(__METHOD__, $d);
        }
        return $d;
    }

    /**
     * pick given columns of the provided $arr
     * @param $arr
     * @param $pick
     * @return array
     */
    public static function arrayPick ($arr, $pick) {
        if (count($pick)>0 && $arr) {
            $opts = [];
            foreach($pick as $n) {
                if (is_array($arr) && isset($arr[$n])) $opts[$n] = $arr[$n];
                else if (is_object($arr) && isset($arr->{$n})) $opts[$n] = $arr->{$n};
            }
            $arr = $opts;
        }
        return $arr;
    }

    /**
     * remove given value from the given array
     *
     * @param $arr
     * @param $value
     * @return array the extracted item
     */
    public static function arrayRemove (&$arr, $value) {
        $index = array_keys($arr, $value, true);
        $ret = [];
        if (count($index)) {
            $ret = array_splice($arr, $index[0], 1);
        }
        return $ret;
    }

    /**
     * shorten a path against the cwd
     *
     * @param $path
     * @return string
     */
    public static function shorten ($path) {
        $path = LocalFs::realpath($path);
        if (substr($path, 0, strlen(getcwd()))===getcwd()) {
            $path = substr($path, strlen(getcwd())+1);
        }
        return $path;
    }
    #endregion

    #region tracing and debug
    //@todo check how symfony manage that, they probably have developed some solution.
    /**
     * Return the current stack trace.
     *
     * @return array
     */
    public static function getStackTrace () {
        $ex = new \Exception('An exception to generate a trace');
        $stack = [];
        foreach($ex->getTrace() as $trace){
            unset($trace['args']);
            $stack[] = (array)$trace;
        }
        return $stack;
    }
    /**
     * helper to traverse a stack trace
     *
     * @param array $stack
     * @param $classType
     * @return array|null
     */
    public static function findCaller ($stack, $classType) {
        $caller = null;
        $lineInfo = null;
        foreach($stack as $trace) {
            if (isset($trace['class'])) {
                if ( is_subclass_of($trace['class'], $classType) || $trace['class']===$classType) {
                    $caller = $trace;
                    if (!isset($caller['line']) && $lineInfo) {
                        $caller = array_merge($lineInfo, $caller);
                    }
                }
            }
            $lineInfo = $trace;
        }
        return $caller;
    }
    #endregion
}
Utils::$stderrHandle = fopen('php://stderr', 'w+');
Utils::$stdoutHandle = fopen('php://stdout', 'w+');