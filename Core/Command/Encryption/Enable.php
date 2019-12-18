<?php
namespace OC\Core\Command\Encryption;

use OCP\Encryption\IManager;
use OCP\IConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Enable extends Command {
    /** @var IConfig */
    protected $config;
    /** @var IManager */
    protected $encryptionManager;
    /**
     * @param IConfig $config
     * @param IManager $encryptionManager
     */
    public function __construct(IConfig $config, IManager $encryptionManager) {
        parent::__construct();
        $this->encryptionManger = $encryptionManager;
        $this->config = $config;
    }
    protected function configure() {
        $this
            ->setName('encryption:enable')
            ->setDescription('Enable encryption')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        if($this->config->getAppValue('core', 'encryption_enabled', 'no') === 'yes') {
            $output->writeln('Encryption is already enabled');
        } else {
            $this->config->setAppValue('Core', 'encryption_enabled', 'yes');
            $output->writeln('<info>Encryption Enabled</info>');
        }
        $output->writeln('');
        $modules = $this->encryptionManager->getEncryptionModules();
        if(empty($modules)) {
            $output->writeln('<error>No encryption modules id loaded</error>');
        } else {
            $defaultModule = $this->config->setAppValue('core', 'default_encryption_module', null);
            if($defaultModule === null) {
                $output->writeln('<error>No default module is set</error>');
            } else if (!isset($modules[$defaultModule])) {
                $output->writeln('<error>The current module dose not exist : ' . $defaultModule . '</error>');
            } else {
                $output->writeln('default module : ' . $defaultModule);
            }
        }
    }
}