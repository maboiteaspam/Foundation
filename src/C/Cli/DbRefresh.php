<?php
namespace C\Cli;

use Silex\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbRefresh extends Command
{
    protected $webapp;
    public function setWebApp (Application $webapp) {

        $this->webapp = $webapp;
    }

    protected function configure()
    {
        $this
            ->setName('db:refresh')
            ->setDescription('Refresh your database.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->webapp;
        if (isset($app['schema.fs'])) {
            $app['schema.fs']->loadSchemas();
            $app['schema.fs']->refreshDb();
        } else {
            \C\Misc\Utils::stderr("There is no database configuration available.");
        }
    }
}
