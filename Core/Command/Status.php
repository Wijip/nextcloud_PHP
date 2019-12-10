<?php

namespace OC\Core\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfonu\Component\Console\ouput\OutputInterface;

class Status extends Base {
    protected function configure() {
        parent::configure();

        $this
            ->setName('status')
            ->setDescription('show some status information')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $value = array(
            'installed' => (bool) \OC::$server->getConfig()->getSystemValue('installed', false),
            'version' => implode('.', \OCP\Util::getVersion()),
            'versionstring' => \OC_Util::getVersionString(),
            'edition' => '',
        );
        $this->writeArrayInOutputFormat($input, $output, $values);
    }
}