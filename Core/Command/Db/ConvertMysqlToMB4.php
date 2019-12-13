<?php
namespace OC\Core\Command\Db;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use OC\DB\MySqlTools;
use OC\Migration\ConsoleOutput;
use OC\Repair\Collation;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\ILogger;
use OCP\IURLGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertMysqlToMB4 extends Command {
    /** @var Iconfig */
    private $config;
    /** @var IDBConnection */
    private $connection;
    /** @var IURLGenerator */
    private $urlGenerator;
    /** @var ILogger */
    private $logger;

    /**
     * @param IConfig $config
     * @param IDBConnection $connection
     * @param IURLGenerator $urlGenerator
     * @param ILogger $logger
     */
    public function __construct(IConfig $config, IDBConnection $connection, IURLGenerator $urlGenerator, ILogger $logger) {
        $this->config = $config;
        $this->connection = $connection;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $logger;
        parent::__construct();
    }
    protected function configure() {
        $this
            ->setName('db:convert-mysql-charset')
            ->setDescription('convert charset of MYSQL/MariaDB to use utf8mb4');
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        if(!$this->connection->getDatabasePlatform() instanceof MysqlPlatform) {
            $output->writeln("this command is only valid for MYSQL/MariaDB database. ");
            return 1;
        }
        $tools = new MysqlTools();
        if(!$tools->supports4ByteCharset($this->connection)) {
            $url = $this->urlGenerator->linkToDocs('admin-mysql-utfmb4');
            $output->writeln("the database is not properly setup to use the charset utf8mb4.");
            $output->writeln("for more information please read the documentation at $url");
            return 1;
        }
        // enable charset
        $this->config->setSystemvalue('mysql.utf8mb4', true);
        //run conversion
        $coll = new Collation($this->config, $this->logger, $this->connection, false);
        $coll->run(new ConsoleOutput($output));
        return 0;
    }
}