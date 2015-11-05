<?php
namespace C\Cli;

use Silex\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CacheUpdate extends Command
{
    protected $webapp;
    public function setWebApp (Application $webapp) {

        $this->webapp = $webapp;
    }

    protected function configure()
    {
        $this
            ->setName('cache:update')
            ->setDefinition([
                new InputArgument('change', InputArgument::REQUIRED, 'Type of change'),
                new InputArgument('file', InputArgument::REQUIRED, 'The path changed'),
            ])
            ->setDescription('Update cached items given a relative file path and the related File System action')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->webapp;

        $file = $input->getArgument('file');
        $change = $input->getArgument('change');

        $k = realpath($file);
        if ($k!==false) $file = $k;

        $watcheds = $app['watchers.watched'];

        foreach ($watcheds as $watched) {
            try{
                /* @var $watched \C\Watch\WatchedInterface */
                $watched->loadFromCache();
            }catch(\Exception $ex){
                \C\Misc\Utils::stderr($watched->getName()." has weirdness in cache and can t be loaded !");
            }
        }

        foreach ($watcheds as $watched) {
            try{
                /* @var $watched \C\Watch\WatchedInterface */
                if ($watched->changed($change, $file)) {
                    \C\Misc\Utils::stdout($watched->getName()." updated with action $change");
                }
            }catch(\Exception $ex) {
                \C\Misc\Utils::stderr($watched->getName()." failed to update !");
                if ($ex->getPrevious())
                    \C\Misc\Utils::stderr($ex->getPrevious()->getMessage());
                else
                    \C\Misc\Utils::stderr($ex->getMessage());
            }
        }
    }
}
