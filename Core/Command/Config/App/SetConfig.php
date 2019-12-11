<?php
namespace OC\Core\Command\Config\App;

use OCP\IConfig;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetConfig extends Base {
    /** @var IConfig */
    protected $config;
    
    /** @param IConfig $config */
    public function __construct(IConfig $config) {
        parent::__construct();
        $this->config = $config;
    }
    protected function configure() {
        parent::configure();
        $this
            ->setName('config:app:set')
            ->setDescription('set an app config value')
            ->addArgument(
                'app',
                InputInterface::REQUIRED,
                'Name of the app'
            )
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Name of the config to set'
            )
            ->addOption(
                'value',
                null,
                InputOption::VALUE_REQUIRED,
                'the new value of the config'
            )
            ->addOption(
                'update-only',
                null,
                InputOption::VALUE_NONE,
                'Only updates the value, if it is not set before, it is not being added'
            )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        $appName = $input->getArgument('app');
        $configName = $input->getArgument('name');
        if(!in_array($configName, $this->config->getAppKeys($appName)) && $input->hasParameterOption('--update-only')) {
            $output->writeln('<comment>config value ' . $configName . ' for app ' .  $appName . ' not update, as it has not been set before.</comment>');
            return 1;
        }
        $configValue = $input->getoptional('value');
        $this->config->setAppValue($appName, $configName, $configValue);
        $output->writeln('<info>config value ' . $configName . ' for app ' . $appName . ' set to ' . $configValue . '</info>');
        return 0;
    }
}