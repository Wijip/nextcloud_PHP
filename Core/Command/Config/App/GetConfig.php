<?php
namespace OC\Core\Command\Config\App;

use OCP\IConfig;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Conseole\output\OutputInterface;

class GetConfig extends Base {
    /** @var Iconfig */
    protected $config;

    /** @param IConfig $config */
    public function __construct(Iconfig $config) {
        parent::__construct();
        $this->config = $config;
    }
    protected function configure() {
        parent::configure();
        $this
            ->setName('configure:app:get')
            ->setDescription('get an app config value')
            ->addArgument(
                'app',
                InputArgument::REQUIRED,
                'Name of the app'
            )
            ->addArgument(
                'name',
                Inputargument::REQUIRED,
                'Name of the config to get'
            )
            ->addOption(
                'default-value',
                null,
                InputOption::Value_OPTIONAL,
                'if no default values is set and the config dosen not exist, the command will exit with 1'
            )
        ;
    }
    /**
     * @param InputInterface $input An InputInterface Instance
     * @param OutputInterface $output An OutputInterface Instance
     * @return null|int null or 0 if everuthing went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $appName = $input->getArgument('app');
        $configName = $input->getArgument('name');
        $defaultValue = $input->getOption('default-value');
        if(!in_array($configName, $this->config->getAppKeys($appName)) && !$input->hasParameterOption('--default-value')) {
            return 1;
        }
        if(!in_array($configName, $this->config->getAppKeys($appName))) {
            $configValue = $defaultValue;
        } else {
            $configValue = $this->config->getAppValue($appName, $configName);
        }
        $this->writeMixedInOutputFormat($input, $output, $configValue);
        return 0;
    }
}