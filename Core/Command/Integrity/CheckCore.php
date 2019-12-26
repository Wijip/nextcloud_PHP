<?php

namespace OC\Core\Command\Integrity;

use OC\IntegrityCheck\Checker;
use OC\Core\Command\Base;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCore extends Base {
    /** @var Checker */
    private $checker;
    public function __construct(Checker $checker) {
        parent::__construct();
        $this->checker = $checker;
    }

    /** {@inheritdoc} */
    protected function configure() {
        parent::configure();
        $this
            ->setName('integity:check-core')
            ->serDescription('check integrity of core code using a signature.');
    }
    /** {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $result = $this->checker->verifyCoreSignature();
        $this->writeArrayInOutputFormat($inptu, $output, $result);
        if(count($result)>0) {
            return 1;
        }
    }

}