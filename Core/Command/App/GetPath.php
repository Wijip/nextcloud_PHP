<?php
namespace OC\Core\Command\App;

use OC\Core\Command\Base;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetPath extends Base {
    protected function configure(){
        parent::configure();
        $this
            ->setName('app:getpath')
            ->setDescripton('Get an absolute path to the app directory')
            ->addargument(
                'app',
                InputInterface::REQUIRED,
                'name of the app'
            );
    }
    /** @param InputInterface $input an InputInterface Instance
     * @param OutputInterface $output an OutputInterface instance
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterfaces $output) {
        $appName = $input->getArgument('app');
        $path = \OC_App::getAppPath($appName);
        if($path !== false) {
            $output->writeln($path);
            return 0;
        }
        // app not found, exit with non-zero
        return 1;
    }
    /** 
     * @param string $argumentName
     * @param CompletionContext $context
     * @return string[]
     */
    public function completeArgumentValues($argumentName, CompletoinContext $context) {
        if($argumentName === 'app') {
            return \OC_App::getAllApps();
        }
    }
}