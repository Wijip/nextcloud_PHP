<?php
namespace OC\Core\Command\Encryption;

use OC\Core\Command\Base;
use OCP\Encryption\IManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListModules extends Base {
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
            ->setName('encryption:list-modules')
            ->setDescription('List all available encryption modules')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $encryptionModules = $this->encryptionManager->getEncryptionModules();
        $defaultEncryptionModuleId = $this->encryptionManager->getDefaultEncryptionId();
        $encModules = array();
        foreach ($encryptionModules as $module) {
            $encModules[$module['id']]['displayName'] = $module['displayName'];
            $encModules[$module['id']]['default'] = $module['id'] === $defaultEncryptionModuleId;
        }
        $this->writeModuleList($input, $output, $encModules);
    }

    /**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @param array $items
	 */
	protected function writeModuleList(InputInterface $input, OutputInterface $output, $items) {
		if ($input->getOption('output') === self::OUTPUT_FORMAT_PLAIN) {
			array_walk($items, function(&$item) {
				if (!$item['default']) {
					$item = $item['displayName'];
				} else {
					$item = $item['displayName'] . ' [default*]';
				}
			});
		}

		$this->writeArrayInOutputFormat($input, $output, $items);
	}
}