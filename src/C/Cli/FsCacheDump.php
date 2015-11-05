<?php
namespace C\Cli;

use Silex\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FsCacheDump extends Command
{
    protected $webapp;
    public function setWebApp (Application $webapp) {

        $this->webapp = $webapp;
    }

    protected function configure()
    {
        $this
            ->setName('fs-cache:dump')
            ->setDescription('Dumps all paths to watch for changes.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->webapp;
        $res = [];

        $watcheds = $app['watchers.watched'];

        foreach ($watcheds as $watched) {
            /* @var $watched \C\Watch\WatchedInterface */
            $watched->resolveRuntime();
        }

        foreach ($watcheds as $watched) {
            /* @var $watched \C\Watch\WatchedInterface */
            $dump = $watched->dump();
            if ($dump) $res[] = $dump;
        }
        echo json_encode($res);
    }
}
