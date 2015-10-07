<?php
namespace C\Misc;

use Psr\Log\LoggerInterface;

class Debug{

    public static $singleton;
    public static function instance () {
        if (!self::$singleton)
            self::$singleton = new Debug();
        return self::$singleton;
    }

    public $groups = [];
    public function displayGroups($groups){
        $this->groups = $groups;
    }

    /**
     * @var LoggerInterface
     */
    public $writer;
    public function setWriter(LoggerInterface $writer){
        $this->writer = $writer;
    }

    public function isGroupToDisplay($group){
        return in_array($group, $this->groups);
    }

    public static function emergency($group, $message, $context=[]) {
        if (self::instance()->isGroupToDisplay($group)) {
            self::instance()->writer->emergency($message, $context);
        }
    }
    public static function alert($group, $message, $context=[]) {
        if (self::instance()->isGroupToDisplay($group)) {
            self::instance()->writer->alert($message, $context);
        }
    }
    public static function critical($group, $message, $context=[]) {
        if (self::instance()->isGroupToDisplay($group)) {
            self::instance()->writer->critical($message, $context);
        }
    }
    public static function error($group, $message, $context=[]) {
        if (self::instance()->isGroupToDisplay($group)) {
            self::instance()->writer->error($message, $context);
        }
    }
    public static function warning($group, $message, $context=[]) {
        if (self::instance()->isGroupToDisplay($group)) {
            self::instance()->writer->warning($message, $context);
        }
    }
    public static function notice($group, $message, $context=[]) {
        if (self::instance()->isGroupToDisplay($group)) {
            self::instance()->writer->notice($message, $context);
        }
    }
    public static function info($group, $message, $context=[]) {
        if (self::instance()->isGroupToDisplay($group)) {
            self::instance()->writer->info($message, $context);
        }
    }
    public static function debug($group, $message, $context=[]) {
        if (self::instance()->isGroupToDisplay($group)) {
            self::instance()->writer->debug($message, $context);
        }
    }
    public static function log($group, $message, $context=[]) {
        if (self::instance()->isGroupToDisplay($group)) {
            self::instance()->writer->log($message, $context);
        }
    }
}