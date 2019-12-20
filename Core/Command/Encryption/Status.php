<?php
namespace OC\Core\Command\Encryption;
use OC\Core\Command\Base;
use OCP\Encryption\IManager;
use symfony\Component\Console\Input\InputInterface;
use symfony\Component\Console\Output\OutputInterface;

class Status extends Base {
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
            ->setName('encryption:status')
            ->setDescription('Lists the current status of encryption')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->writeArrayOutputFormat($input, $output, [
            'enabled' => $this->encryptionManager->isEnabled(),
            'defaultModule' => $this->encryptionManager->getDefaultEncryptionModuleId(),
        ]);
    }
}