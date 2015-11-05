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
        if (isset($app['capsule.connections'])) {
            $connections = $app['capsule.connections'];
            foreach ($connections as $connection => $options) {
                if ($options["driver"]==='sqlite') {
                    if ($options["database"]!==':memory:') {
                        $exists = LocalFs::file_exists($options['database']);
                        if (!$exists) {
                            $dir = dirname($options["database"]);
                            if (!LocalFs::is_dir($dir)) LocalFs::mkdir($dir, 0700, true);
                            LocalFs::touch($options["database"]);
                        }
                    }
                }
            }
            $app['capsule.schema']->loadSchemas();
            $app['capsule.schema']->cleanDb();
            $app['capsule.schema']->initDb();
        } else {
            \C\Misc\Utils::stderr("There is no database configuration available.");
        }
    }
}
