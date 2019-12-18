<?php
namespace OC\Core\Command\Encryption;

use OC\Encryption\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowKeyStorageRoot extends Command {
    /** @var Util */
    protected $util;
    /** @param Util $util */
    public function __construct(Util $util) {
        parent::__construct();
        $this->util = $util;
    }
    protected function configure() {
        parent::configure();
        $this
            ->setName('encryption:show-key-storage-root')
            ->setDescription('Show current key storage root');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $currentRoot = $this->util->getKeyStorageRoot();
        $rootDescription = $currentRoot !== '' ? $currentRoot : 'default storage location (data/)';
        $output->writeln("current key storage root: <info>$rootDescription</info>");
    }
}