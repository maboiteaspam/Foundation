<?php
namespace C\Cli;

use Silex\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CacheInit extends Command
{
    protected $webapp;
    public function setWebApp (Application $webapp) {

        $this->webapp = $webapp;
    }

    protected function configure()
    {
        $this
            ->setName('cache:init')
            ->setDescription('Generate cached items')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->webapp;
        $watcheds = $app['watchers.watched'];

        foreach ($watcheds as $watched) {
            /* @var $watched \C\Watch\WatchedInterface */
            $watched->clearCache();
        }

        foreach ($watcheds as $watched) {
            /* @var $watched \C\Watch\WatchedInterface */
            $watched->resolveRuntime();
        }

        foreach ($watcheds as $watched) {
            /* @var $watched \C\Watch\WatchedInterface */
            $dump = $watched->build()->saveToCache();
            echo $watched->getName()." signed with ".$dump['signature']."\n";
        }
    }
}
