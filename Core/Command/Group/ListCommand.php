<?php
namespace OC\Core\Command\Group;

use OC\Core\Command\Base;
use OCP\IGroup;
use OCP\IGroupManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Base {
    /** @var IGroupManager */
    protected $groupManager;

    /**
     * @param IGroupManager $groupManager
     */
    public function __construct(IGroupManager $groupManager) {
        $this->groupManager = $groupManager;
        parent::__construct();
    }
    protected function configure() {
		$this
			->setName('group:list')
			->setDescription('list configured groups')
			->addOption(
				'limit',
				'l',
				InputOption::VALUE_OPTIONAL,
				'Number of groups to retrieve',
				500
			)->addOption(
				'offset',
				'o',
				InputOption::VALUE_OPTIONAL,
				'Offset for retrieving groups',
				0
			)->addOption(
				'output',
				null,
				InputOption::VALUE_OPTIONAL,
				'Output format (plain, json or json_pretty, default is plain)',
				$this->defaultOutputFormat
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$groups = $this->groupManager->search('', (int)$input->getOption('limit'), (int)$input->getOption('offset'));
		$this->writeArrayInOutputFormat($input, $output, $this->formatGroups($groups));
	}

	/**
	 * @param IGroup[] $groups
	 * @return array
	 */
	private function formatGroups(array $groups) {
		$keys = array_map(function (IGroup $group) {
			return $group->getGID();
		}, $groups);
		$values = array_map(function (IGroup $group) {
			return array_keys($group->getUsers());
		}, $groups);
		return array_combine($keys, $values);
	}
}