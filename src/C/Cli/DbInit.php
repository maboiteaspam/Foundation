<?php
namespace C\Cli;

use C\FS\LocalFs;
use Silex\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbInit extends Command
{
    protected $webapp;
    public function setWebApp (Application $webapp) {

        $this->webapp = $webapp;
    }

    protected function configure()
    {
        $this
            ->setName('db:init')
            ->setDescription('Initialize your database. Clear all, construct schema, insert fixtures.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->webapp;
        if (isset($app['schema.fs'])) {
            $app['schema.fs']->loadSchemas();
            $app['schema.fs']->cleanDb();
            $app['schema.fs']->initDb();
        } else {
            \C\Misc\Utils::stderr("There is no database configuration available.");
        }
    }
}
