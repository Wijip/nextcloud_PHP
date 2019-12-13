<?php
namespace OC\Core\Command\Db\Migrations;

use Doctrine\DBAL\Schema\Schema;
use OC\DB\MDB2SchemaReader;
use OC\DB\MigrationService;
use OC\Migration\ConsoleOutput;
use OCP\App\IAppManager;
use OCP\IConfig;
use OCP\IDBConnection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateFromSchemaFileCommand extends GenerateCommand {
    /** @var IConfig */
    protected $config;
    public function __construct(IConfig $config, IAppManager $appManager, IDBConnection $connection) {
        parent::__construct($connection, $appManager);
        $this->config = $config;
    }

    protected function configure() {
        parent::configure();
        $this->setName('migration:generate-from-schema');
    }
    public function execute(InputInterface $input, OutputInterface $output) {
        $appName = $input->getArgument('app');
        $version = $input->getArgument('version');
        
        if (!preg_match('/^\d{1,16}$/',$version)) {
			$output->writeln('<error>The given version is invalid. Only 0-9 are allowed (max. 16 digits)</error>');
			return 1;
        }
        $schemaFile = $this->appManager->getAppPath($appName) . '/appinfo/database.xml';
        if(!file_exists($schemaFile)) {
            $output->writeln('<error>App ' . $appName . ' does not have a database.xml file</error>');
            return 2;
        }
        
        $reader = new MDB2SchemaReader($this->config, $this->connection->getDatabasePlatform());
        $schema = new Schema();
        $reader->loadSchemaFromFile($schemaFile, $schema);
        $schemaBody = $this->schemaToMigration($schema);
        $ms = new MigrationService($appName, $this->connection, new ConsoleOutput($output));
        $date = date('YmdHis');
        $path = $this->generateMigration($ms, 'Version' . $version . 'Date' . $date, $schemaBody);
        $output->writeln("New Migration class has been generated to <info>$path</info>");
        return 0;
    }
    /**
	 * @param Schema $schema
	 * @return string
	 */
	protected function schemaToMigration(Schema $schema) {
		$content = <<<'EOT'
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

EOT;

		foreach ($schema->getTables() as $table) {
			$content .= str_replace('{{table-name}}', substr($table->getName(), 3), <<<'EOT'

		if (!$schema->hasTable('{{table-name}}')) {
			$table = $schema->createTable('{{table-name}}');

EOT
			);

			foreach ($table->getColumns() as $column) {
				$content .= str_replace(['{{name}}', '{{type}}'], [$column->getName(), $column->getType()->getName()], <<<'EOT'
			$table->addColumn('{{name}}', '{{type}}', [

EOT
				);
				if ($column->getAutoincrement()) {
					$content .= <<<'EOT'
				'autoincrement' => true,

EOT;
				}
				$content .= str_replace('{{notnull}}', $column->getNotnull() ? 'true' : 'false', <<<'EOT'
				'notnull' => {{notnull}},

EOT
				);
				if ($column->getLength() !== null) {
					$content .= str_replace('{{length}}', $column->getLength(), <<<'EOT'
				'length' => {{length}},

EOT
					);
				}
				$default = $column->getDefault();
				if ($default !== null) {
					if (is_string($default)) {
						$default = "'$default'";
					} else if (is_bool($default)) {
						$default = ($default === true) ? 'true' : 'false';
					}
					$content .= str_replace('{{default}}', $default, <<<'EOT'
				'default' => {{default}},

EOT
					);
				}
				if ($column->getUnsigned()) {
					$content .= <<<'EOT'
				'unsigned' => true,

EOT;
				}

				$content .= <<<'EOT'
			]);

EOT;
			}

			$content .= <<<'EOT'

EOT;

			$primaryKey = $table->getPrimaryKey();
			if ($primaryKey !== null) {
				$content .= str_replace('{{columns}}', implode('\', \'', $primaryKey->getUnquotedColumns()), <<<'EOT'
			$table->setPrimaryKey(['{{columns}}']);

EOT
				);
			}

			foreach ($table->getIndexes() as $index) {
				if ($index->isPrimary()) {
					continue;
				}

				if ($index->isUnique()) {
					$content .= str_replace(
						['{{columns}}', '{{name}}'],
						[implode('\', \'', $index->getUnquotedColumns()), $index->getName()],
						<<<'EOT'
			$table->addUniqueIndex(['{{columns}}'], '{{name}}');

EOT
					);
				} else {
					$content .= str_replace(
						['{{columns}}', '{{name}}'],
						[implode('\', \'', $index->getUnquotedColumns()), $index->getName()],
						<<<'EOT'
			$table->addIndex(['{{columns}}'], '{{name}}');

EOT
					);
				}
			}

			$content .= <<<'EOT'
		}

EOT;
		}

		$content .= <<<'EOT'
		return $schema;
EOT;

		return $content;
	}
}