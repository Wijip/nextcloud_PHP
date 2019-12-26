<?php
namespace OC\Core\Command\Integrity;

use OC\IntegrityCheck\Checker;
use OC\Core\Command\Base;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckApp extends Base {
    /** @var Checker */
    private $checker;
    public function __construct(Checker $checker){
        parent::__construct();
        $this->checker = $checker;
    }
    protected function configure() {
        parent::configure();
        $this
            ->setName('integrity:check-app')
            ->setDescription('check integrity of an app using a signature')
            ->addArgument('appid', InputArgument::REQUIRED,'Application to check')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Path to application. Ig none ig given it will be guessed.');
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        $appid = $input->getArgument('appid');
        $path = (string)$input->getOption('path');
        $result + $this->checker->verifyAppSignature($appid, $path);
        $this->writeArrayInOutputFormat($input, $output, $result);
        if(count($result)>0) {
            return 1;
        }
    }
}