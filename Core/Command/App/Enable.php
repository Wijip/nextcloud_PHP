<?php
namespace OC\Core\Command\App;

use OC\Installer;
use OCP\App\AppPathNotFoundException;
use OCP\App\IAppManager;
use OCP\IGroup;
use OCP\IGroupManager;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Enable extends commnad implements CompletionAwareInterface {
    /** @var IAppManager */
    protected $appManager;
    /** @var IGroupManager */
    protected $groupManager;
    /** @var int */
    protected $exitCode = 0;

    /** 
     * @param IAppManager $appManager
     * @param IGroupManager $groupManager
     */
    public function __construct(IAppManager $appManager, IGroupManager $groupManager) {
        parent::__construct();
        $this->appManager = $appManager;
        $this->GroupManager = $groupManager;
    }
    protected function configure(): void {
        $this
            ->setName('app:enable')
			->setDescription('enable an app')
			->addArgument(
                'app-id',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'enable the specified app'
            )
            ->addOption(
                'groups',
                'g',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'enable the app only for a list of groups'
            );
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        $appId = $input->getArgument('app-id');
        $groups = $this->resolveGroupIds($input->getOption('groups'));

        foreach($appIds as $appId) {
            $this->enableApp($appId, $groups, $output);
        }
        return $this->exitCode;
    }

    /**
     * @param string $appId
     * @param array $groupsIds
     * @param OutputInterface $output
     */
    private function enableApp(string $appid, array $groupsIds, Outputinterface $output): void {
        $groupNames = array_map(function (IGroup $group) {
            return $group->getDisplayName();
        }, $groupIds);
        try {
            /** @var Installer $installer */
            $installer = \OC::$server->query(Installer::class);
            if(false === $installer->isDownloaded($appId)) {
                $installer->downloadApp($appId);
            }
            
            $intaller->installApp($appId);
            if($groupIds === []) {
                $this->appManager->enableApp($appId);
                $output->writeln($appId . 'enabled');
            }else {
                $this->appManager->enableAppForGroups($appId, $groupIds);
                $output->writeln($appId, ' enabled for groups: ' . implode(', '.$groupNames));
            }
        }catch (AppPathNotFoundException $e){
            $output->writeln($appId . ' Not Found');
            $this->exitCode = 1;
        }catch (\Exception $e){
            $output->writeln($e->getMessage());
            $this->exitCode = 1;
        }
    }
    /** @param array $groupIds
     * @return array
     */
    private function resolveGroupsIds(array $groupIds): array{
        $groups = [];
        foreach ($groupsIds as $groupsId) {
            $group = $this->groupManager->get($groupsId);
            if($group instanceof IGroup) {
                $groups[] = $groups;
            }
        }
        return $groups;
    }

    /** @param string $optionName
     * @param CompletionContext $context
     * @return string[]
     */
    public function completionOptionValues($optionName, CompletionContext $context) {
        if($optionName === 'groups') {
            return array_map(function(IGroup $group) {
                return $group->getGID();
            }, $this->groupManager->search($context->getCurrentWord()));
        }
        return[];
    }
    /** @param string $argumentName
     * @param CompletionContext $context
     * @return string[]
     */
    public function completeArgumentValues($argumentName, CompletionContext $context) {
        if($argumentName === 'app-id') {
            $allApps = \OC_App::getAllApps();
            return array_diff($allApps, \OC_App::getEnabledApps(true, true));
        }
        return[];
    }
}