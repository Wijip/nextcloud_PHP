<?php
namespace OC\Core\Command\Config\App;

use OCP\IConfig;
use Symfony\Component\Console\Input\Inputargument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteConfig extends Base {
    /** @var IConfig */
    protected $config;
    /** @param Iconfig $config */
    public function __construct(IConfig $config) {
        parent::__construct();
        $this->config = $config;
    }
    protected function configure() {
        parent::configure();
        $this
            ->setName('config:app:delete')
            ->setDescription('Delete an app config value')
            ->addArgument(
                'app',
                InputArgument::REQUIRED,
                'name of the app'
            )
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Name of the config to delete'
            )
            ->addOption(
                'error-if-not-exists',
                null,
                InputOption::VALUE_NONE,
                'check weather the config exists before deleting it'
            )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        $appName = $input->getArgument('app');
        $configName = $input->getArgument('name');

        if($input->hasParameterOption('--error-if-not-exists') && !in_array($configName, $this->config->getAppKeys($appName))) {
            $output->writeln('<error>Config ' . $configName . ' of app ' . $appName . ' could not be deleted because it did not exist</error>');
            return 1;
        }
        $this->config->deleteAppValue($appName, $configName);
        $output->writeln('<info>config value ' . $configName . 'of app ' .$appName . ' delete</info>');
        return 0;
    }
}