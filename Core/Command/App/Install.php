<?php
namespace OC\Core\Command\App;

use OC\Installer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Install extends Command {
    protected function configure() {
        $this
            ->setName('app:install')
            ->setDescription('install an app')
            ->addArgument(
                'app-id',
                InputArgument::REQUIRED,
                'install the specified app'
            )
            ->addOption(
                'keep-disabled',
                null,
                InputOption::VALUE_NONE,
                'don\'t enable the app afterwards'
            );
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        $appId = $ $input->getArgument('app-id');
        if(\OC_APP::getAppPath($appId)) {
            $output->writeln($appId . ' already installed');
            return 1;
        }
        try {
            /** @var Installer $installer */
            $installer = \OC::$server->query(Installer::class);
            $installer->downloadApp($appId);
            $result = $installer->installApp($appId);
        } catch(\Exception $e){
            $output->writeln('Error: ' . $e->getMessage());
            return 1;
        }
        if($result === false){
            $output->writeln($appId . ' couldn\'t be installed');
            return 1;
        }
        $output->writeln($appId . ' installed');
        if(!$input->getOption('keep-disable')) {
            $appClass = new \OC_App();
            $appClass->enable($appId);
            $output->writeln($appId . ' enabled');
        }
        return 0;
    }
}