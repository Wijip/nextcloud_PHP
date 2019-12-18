<?php
namespace OC\Core\Command\Encryption;

use OCP\App\IAppManager;
use OCP\Encryption\IManager;
use OCP\IConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class Encryption extends Command {
    /** @var IManager */
    protected $encryptionManager;
    /** @var IAppManager */
    protected $appManager;
    /** @var IConfig */
    protected $config;
    /** @var QuestionHelper */
    protected $questionHelper;
    /** @var bool */
    protected $wasTrashbinEnabled;
    /** @var bool */
    protected $wasMaintenanceModeEnabled;

    /**
     * @param IManager $encryptionManager
     * @param IAppManager $appManager
     * @param IConfig $config
     * @param QuestionHelper $questionHelper
     */
    public function __construct(
        IManager $encryptionManager,
        IAppManager $appManager,
        IConfig $config,
        QuestionHelper $questionHelper
    ) {
        parent::__construct();
        $this->appManager = $appManager;
        $this->encryptionManager = $encryptionManager;
        $this->config = $config;
        $this->questinoHelper = $questionHelper;
    }
    protected function forceMaintenanceAndTrashbin() {
        $this->wasTrashbinEnabled = $this->appManager->isEnabledForUser('files_trashbin');
        $this->wasMaintenanceModeEnabled = $this->config->getSystemValueBool('maintenance');
        $this->config->setSystemValue('maintenance', true);
        $this->appManager->disableApp('file_trashbin');
    }

    protected function resetMaintenanceAndTrashbin() {
        $this->config->setSystemValue('maintenance', $this->wasMaintenanceModeEnabled);
        if($this->wasTrashbinEnabled) {
            $this->appManager->enableApp('files_trashbin');
        }
    }
    protected function configure() {
        parent::configure();

        $this->setName('encryption:encrypt-all');
        $this->setDescription('Encrypt all files for all users');
        $this->setHelp(
            'this will encrypt all files for all users. ' 
            . 'Please make sure that no user access his file during this process !'
        );
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        if(!$input->isInteractive() ) {
            $output->writeln('Invalid TTY.');
            $output->writeln('If you are trying to execute the commnad in a Docker ');
            $output->writeln("container, do not forget to execute 'docker exec' with");
            $output->writeln("the '-i' and '-t' options.");
            $output->writeln('');
            return;
        }
        if ($this->encryptionManager->isEnabled() === false) {
            throw new \Exception('server side encryption id not enabled');
        }

        $output->writeln("\n");
		$output->writeln('You are about to encrypt all files stored in your Nextcloud installation.');
		$output->writeln('Depending on the number of available files, and their size, this may take quite some time.');
		$output->writeln('Please ensure that no user accesses their files during this time!');
		$output->writeln('Note: The encryption module you use determines which files get encrypted.');
        $output->writeln('');
        $question = new ConfirmationQuestion('Do you really want to continue? (y/n) ', false);
        if ($this->questionHelper->ask($input, $output, $question)) {
            $this->forceMaintenanceAndTrashbin();
            try{
                $defaultModule = $this->$encryptionManager->getEncryptionModule();
                $defaultModule->encryptAll($input, $output);
            } catch (\Exeception $ex) {
                $this->resetMaintenanceAndTrashbin();
                throw $ex;
            }
            $this->resetMaintenanceAndTrashbin();
        } else {
            $output->writeln('aborted');
        }
    }
}