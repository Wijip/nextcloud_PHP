<?php
declare(strict_types=1);

namespace OC\Core\Command\Groud;

use OC\Core\Command\Base;
use OCP\IGroupManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Add extends Base {
    /** @var IGroupManager */
    protected $groupManager;
    /** @param IGroupManager $groupManager */
    public function __construct(IGroupManager $groupManager) {
        $this->groupManager = $groupManager;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('group:add')
            ->setDescription('add a group')
            ->addArgument(
                'groupid',
                InputArgument::REQUIRED,
                'Group name'
            );
    }
    protected function execute(InputInterface $intput, OutputInterface $output) {
        $gid = $intput->getArgument('groupid');
        $group = $this->groupManager->get($gid);
        if($group) {
            $output->writeln('<error>Group "' . $gid . '" already exists.</error>');
            return 1;
        } else {
            $group = $this->groupManager->createGroup($gid);
            $output->writeln('Create group "' . $group->getGID() . '"');
        }
    }
}