<?php
namespace OC\Core\Command\Db;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Types\Type;
use OC\DB\SchemaWrapper;
use OCP\IDBConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ConvertfilecacheBigInt extends Command {
    /** @var IDBConnection */
    private $connection;
    /** @param IDBConnection $connection */
    public function __construct(IDBConnection $connection) {
        $this->connection = $connection;
        parent::__construct();
    }
    protected function configure() {
        $this
            ->setName('db:convert-filecache-bigint')
            ->setDescription('convert the ID columns of the filecache to BigInt');
    }
    protected function getColumnsByTable() {
		// also update in CheckSetupController::hasBigIntConversionPendingColumns()
		return [
			'activity' => ['activity_id', 'object_id'],
			'activity_mq' => ['mail_id'],
			'filecache' => ['fileid', 'storage', 'parent', 'mimetype', 'mimepart', 'mtime', 'storage_mtime'],
			'mimetypes' => ['id'],
			'storages' => ['numeric_id'],
		];
    }
    protected function execute(InputInterface $input, OutputInterface $output) {

		$schema = new SchemaWrapper($this->connection);
		$isSqlite = $this->connection->getDatabasePlatform() instanceof SqlitePlatform;
		$updates = [];

		$tables = $this->getColumnsByTable();
		foreach ($tables as $tableName => $columns) {
			if (!$schema->hasTable($tableName)) {
				continue;
			}

			$table = $schema->getTable($tableName);

			foreach ($columns as $columnName) {
				$column = $table->getColumn($columnName);
				$isAutoIncrement = $column->getAutoincrement();
				$isAutoIncrementOnSqlite = $isSqlite && $isAutoIncrement;
				if ($column->getType()->getName() !== Type::BIGINT && !$isAutoIncrementOnSqlite) {
					$column->setType(Type::getType(Type::BIGINT));
					$column->setOptions(['length' => 20]);

					$updates[] = '* ' . $tableName . '.' . $columnName;
				}
			}
		}

		if (empty($updates)) {
			$output->writeln('<info>All tables already up to date!</info>');
			return 0;
		}

		$output->writeln('<comment>Following columns will be updated:</comment>');
		$output->writeln('');
		$output->writeln($updates);
		$output->writeln('');
		$output->writeln('<comment>This can take up to hours, depending on the number of files in your instance!</comment>');

		if ($input->isInteractive()) {
			$helper = $this->getHelper('question');
			$question = new ConfirmationQuestion('Continue with the conversion (y/n)? [n] ', false);

			if (!$helper->ask($input, $output, $question)) {
				return 1;
			}
		}

		$this->connection->migrateToSchema($schema->getWrappedSchema());

		return 0;
	}
}