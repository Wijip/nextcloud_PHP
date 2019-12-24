<?php
namespace OC\Core\Command\Group;

use OC\Core\Command\Base;
use OCP\IGroupManager;
use OCP\IUserManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveUser extends Base {
	/** @var IUserManager */
	protected $userManager;
	/** @var IGroupManager */
	protected $groupManager;

	/**
	 * @param IUserManager $userManager
	 * @param IGroupManager $groupManager
	 */
	public function __construct(IUserManager $userManager, IGroupManager $groupManager) {
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('group:removeuser')
			->setDescription('remove a user from a group')
			->addArgument(
				'group',
				InputArgument::REQUIRED,
				'group to remove the user from'
            )
            ->addArgument(
				'user',
				InputArgument::REQUIRED,
				'user to remove from the group'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$group = $this->groupManager->get($input->getArgument('group'));
		if(is_null($group)) {
			$output->writeln('<error>group not found</error>');
			return 1;
		}
		$user = $this->userManager->get($input->getArgument('user'));
		if(is_null($user)) {
			$output->writeln('<error>user not found</error>');
			return 1;
		}
		$group->removeUser($user);
	}
}
