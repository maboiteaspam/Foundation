<?php
namespace C\Fixture;

use C\Stream\StreamConcat;
use C\Stream\StreamFlow;

/**
 * Class Generator
 * helps to generate entities
 * to use in fixtures
 *
 * @package C\Fixture
 */
class Generator{

    /**
     * push $len times
     * a clone of $what
     * modified with $transform
     * returns the resulting array $results of data
     *
     * @param $what
     * @param $transform
     * @param int $len
     * @return \ArrayObject
     */
    public static function generate ($what, $transform, $len=10) {
        $results = new \ArrayObject();
        $concat = new StreamConcat();
        StreamFlow::demultiplex($len)
            ->pipe($transform)
            ->pipe($concat->appendTo($results))
            ->write($what);
        return $results;
    }

}

// see
// https://github.com/maboiteaspam/StreamObjectTransform
// https://github.com/maboiteaspam/BlogData