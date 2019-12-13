<?php
namespace OC\Core\Command\Db\Migrations;


use OC\DB\MigrationService;
use OC\Migration\ConsoleOutput;
use OCP\App\IAppManager;
use OCP\IConfig;
use OCP\IDBConnection;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteCommand extends Command Implements CompletionAwareInterface {
    /** @var IDBConnection */
    private $connection;
    /** @var IConfig */
    private $config;
    /** @var IAppManager */
    protected $appManager;

    /** 
     * @param IDBConnection $connection
     * @param IConfig $config
     * @param IAppManager $appManager
     */
    public function __construct(IDBConnection $connection, IAppManager $appManager, IConfig $config) {
        $this->connection = $connection;
        $this->config = $config;
        parent::__construct();
    }
    protected function configure() {
        $this
            ->setName('migration:execute')
            ->setDescription('Execute a single migration version manually.')
            ->addArgument('app', InputArgument::REQUIRED, 'name of the app this migration command shall work on')
            ->addArgument('version', InputArgument::REQUIRED, 'the version the execute,',null);
        
            parent::configure();
    }
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output) {
        $appName = $input->getArgument('app');
        $ms = new MigrationService($appName, $this->connection, new ConsoleOutput($output));
        $version = $input->getArgument('version');
        if($this->config->getSystemvalue('debug', false) === false) {
            $olderVersions = $ms->getMigratedVersion();
            $olderVersions[] = '0';
            $olderVersions[] = 'prev';
            if(in_array($version, $olderVersions, true)) {
                $output->writeln('<error>Can not go back to previous migration without debug enabled</error>');
                return 1;
            }
        }
        $ms->executeStep($version);
        return 0;
    }

    /**
     * @param string $optionName
     * @param CompletionContext $context
     * @return string[]
     */
    public function completeOptionValues($optionName, CompletionContext $context) {
        return [];
    }
    /**
     * @param string $argumentName
     * @param CompletionContext $context
     * @return string[]
     */
    public function completeArgumentValues($argumentName, CompletionContext $context) {
        if($argumentName == 'app') {
            $allApps = \OC_App::getAllApps();
            return array_diff($allApps, \OC_App::getEnabledApps(true, true));
        }
        if($argumentName === 'version') {
            $appName = $context->getWordAtIndex($context->getWordIndex() - 1);
            $ms = new MigrationService($appName, $this->connection);
            $migrations = $ms->getAvailableVersion();
            array_unshift($migrations, 'next','latest');
            return $migrations;
        }
        return[];
    }
}