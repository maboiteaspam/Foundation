<?php

namespace C\FS;
use C\TagableResource\TagResolverInterface;


class KnownFsTagResolver implements TagResolverInterface {

    /**
     * @var KnownFS
     */
    public $fs;

    public function __construct(KnownFS $fs) {
        $this->fs = $fs;
    }

    /**
     * @param $file
     * @return string
     */
    public function resolve ($file) {
        $file = is_array($file)?$file['item']:$file;
        $template = $this->fs->get($file);
        $h = '';
        if ($template) {
            $h .= $template['sha1'].$template['dir'].$template['name'];
        } else if(LocalFs::file_exists($file)) {
            $h .= LocalFs::file_get_contents($file);
        } else {
            // that is bad, it means we have registered files
            // that does not exists
            // or that can t be located back.
            //
            // you may have forgotten somewhere
            // $app['intl.fs']->register(__DIR__.'/path/to/templates/', 'ModuleName');
        }
        return $h;
    }

}