<?php
declare(strict_types=1);
namespace OC\Core\Command\Group;
use OC\Core\Command\Base;
use OCP\IGroupManager;
use symfony\Component\Console\Input\InputArgument;
use symfony\Component\Console\Input\InputInterface;
use symfony\Component\Console\Output\OutputInterface;

Class Delete extends Base {
    /** @var IGroupManager */
    protected $groupManager;
    /** @param IGroupManager $groupManager */
    public function __construct(IGroupManager $groupManager) {
        $this->groupManager = $groupManager;
        parent::__construct();
    }
    protected function configure() {
        $this
            ->setName('group:delete')
            ->setDescription('Remove a group')
            ->addArgument(
                'groupid',
                InputArgument::REQUIRED,
                'Group Name'
            )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        $gid = $input->getArgument('groupid');
        if ($gid === 'admin') {
            $output->writeln('<error>Group "' . $gid . '" cloud not be deleted.</error>');
            return 1;
        }
        if(! $this->groupManager->groupExists($gid)) {
            $output->writeln('<error>Group "' . $gid . '" does not exists.</error>');
            return 1;
        }
        $group = $this->groupManager->get($gid);
        if($group->delete()){
            $output->writeln('Group "' . $gid . '" was removed');
        } else {
            $output->writeln('<error>Group "' . $gid . '" could not be deleted. please check the logs.</error>');
            return 1;
        }
    }
}