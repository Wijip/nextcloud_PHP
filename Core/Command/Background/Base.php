<?php
namespace OC\Core\Command\Background;
use \OCP\IConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Base extends Command {
    abstract protected function getMode();
    /** @var \OCP\IConfig; */
    protected $config;

    /**
     * @param \OCP\IConfig $config
     */
    public function __construct(IConfig $config){
        $this->config = $config;
        parent::__construct();
    }
    protected function configure(){
        $mode = $this->getMode();
        $this
            ->setName("background:$mode")
            ->setDescription("Use $mode to run background jobs");
    }
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $mode = $this->getMode();
        $this->config->setAppValue('core', 'backgroundjobs_mode', $mode);
        $output->writeln("set mode for background jobs to '$mode'");
    }
}