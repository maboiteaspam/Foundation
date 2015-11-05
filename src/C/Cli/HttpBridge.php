<?php
namespace C\Cli;

use Silex\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HttpBridge extends Command
{
    protected $webapp;
    public function setWebApp (Application $webapp) {

        $this->webapp = $webapp;
    }

    protected function configure()
    {
        $this
            ->setName('http:bridge')
            ->setDescription('Generate an http bridge file for your webserver.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->webapp;
        if (isset($app['assets.bridger'])) {
            $app['assets.bridger']->generate(
                $app['assets.bridge_file_path'],
                $app['assets.bridge_type'],
                $app['assets.fs']
            );
        }
    }
}
