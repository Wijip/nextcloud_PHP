<?php
namespace OC\Core\Command\Encryption;

use OCP\Encryption\IManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetDefaultModule extends Command {
    /** @var IManager */
    protected $encryptionManager;
    /** @param IManager $encryptionManager */
    public function __construct(IManager $encryptionManager) {
        parent::__construct();
        $this->encryptionManager = $encryptionManager;
    }
    protected function configure() {
        parent::configure();
        $this
            ->setName('encryption:set-default-module')
            ->setDescription('set the encryption default module')
            ->addArgument(
                'module',
                InputArgument::REQUIRED,
                'ID of the encryption module that should be used'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $moduleId = $input->getArgument('module');
        if($moduleId === $this->encryptionManager->getDefaultEncryptionModuleId()) {
            $output->writeln('"' . $moduleId . '"" is already the default module');
        } else if ($this->encryptionManager->setDefaultEncryptionModule($module)) {
            $output->writeln('<info>Set Default module to "' . $moduleId . '"</info>');
        } else {
            $output->writeln('<error> The Specified module "' . $moduleId . '" does not exist</error>');
        }
    }
}