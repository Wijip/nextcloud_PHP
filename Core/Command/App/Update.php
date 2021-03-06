<?php
namespace OC\Core\Command\App;

use OCP\App\IAppManager;
use OC\Installer;
use OCP\ILogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Update extends Command {
    /** @var IAppManager */
    protected $manager;
    /** @var Installer */
    private $installer;
    /** @var ILogger */
    private $logger;

    /**
     * @param IAppManager $manager
     * @param Installer $installer
     */
    public function __construct(IAppManager $manager, Installer $installer, ILogger $logger) {
        parent::__construct();
        $this->manager = $manager;
        $this->installer = $installer;
        $this->logger = $logger;
    }
    protected function configure() {
		$this
			->setName('app:update')
			->setDescription('update an app or all apps')
			->addArgument(
				'app-id',
				InputArgument::OPTIONAL,
				'update the specified app'
			)
			->addOption(
				'all',
				null,
				InputOption::VALUE_NONE,
				'update all updatable apps'
			)
			->addOption(
				'showonly',
				null,
				InputOption::VALUE_NONE,
				'show update(s) without updating'
			)

		;
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
		$singleAppId = $input->getArgument('app-id');

		if ($singleAppId) {
			$apps = array($singleAppId);
			try {
				$this->manager->getAppPath($singleAppId);
			} catch (\OCP\App\AppPathNotFoundException $e) {
				$output->writeln($singleAppId . ' not installed');
				return 1;
			}

		} else if ($input->getOption('all') || $input->getOption('showonly')) {
			$apps = \OC_App::getAllApps();
		} else {
			$output->writeln("<error>Please specify an app to update or \"--all\" to update all updatable apps\"</error>");
			return 1;
		}

		$return = 0;
		foreach ($apps as $appId) {
			$newVersion = $this->installer->isUpdateAvailable($appId);
			if ($newVersion) {
				$output->writeln($appId . ' new version available: ' . $newVersion);

				if (!$input->getOption('showonly')) {
					try {
						$result = $this->installer->updateAppstoreApp($appId);
					} catch(\Exception $e) {
						$this->logger->logException($e, ['message' => 'Failure during update of app "' . $appId . '"','app' => 'app:update']);
						$output->writeln('Error: ' . $e->getMessage());
						$return = 1;
					}

					if ($result === false) {
						$output->writeln($appId . ' couldn\'t be updated');
						$return = 1;
					} else if($result === true) {
						$output->writeln($appId . ' updated');
					}
				}
			}
		}

		return $return;
	}
}
