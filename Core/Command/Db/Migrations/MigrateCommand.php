<?php
namespace OC\Core\Command\Db\Migrations;


use OC\DB\MigrationService;
use OC\Migration\ConsoleOutput;
use OCP\IDBConnection;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command implements CompletionAwareInterface {
    /** @var IDBConnection */
    private $connection;
    /**
     * @param IDBConnection $connection
     */
    public function __construct(IDBConnection $connection) {
        $this->connection = $connection;
        parent::__construct();
    }
    protected function configure() {
        $this
            ->setName('migration:migrate')
            ->setDescription('Execute a migration to a specified version or the latest available version')
            ->addArgument('app', InputArgument::REQUIRED, 'Name of the app this migration command shall work on')
            ->addArgument('version', InputArgument::OPTIONAL, 'The Version number (YYYYMMDDHHMMSS) or alias (first, prev, next, latest) to migrate to.','latest');
        
        parent::configure();
    }
    public function execute(InputInterface $input, OutputInterface $output) {
        $appname = $input->getArgument('app');
        $ms = new MigrationService($appName, $this->connection, new ConsoleOutput($output));
        $version = $input->getArgument('version');
        $ms->migrate($version);
    }
    /**
     * @param string $optionName
     * @param CompletionContext $context
     * @return stirng[]
     */
    public function completeOtionValues($optionName, CompletionContext $context) {
        return [];
    }
    /**
     * @param string $argumentName
     * @param CompletionContext $context
     * @return string[]
     */
    public function completeArgumentValues($argumentName, CompletionContext $context) {
        if($argumentName === 'app') {
            $allApps = \OC_App::getAllApps();
            return array_diff($allApps, \OC_App::getEnabledApps(true, true));
        }
        if ($argumentName === 'version') {
            $appName = $context->getWordAtIndex($context->getWordIndex() - 1);
            $ms = new MigrationService($appName, $this->connection);
            $migration = $ms->getAvailableVersion();
            array_unshift($migration, 'next', 'latest');
            return $migration;
        }
        return [];
    }
}