<?php
namespace OC\Core\Command;

use OC\SystemConfig;
use Symfony\Component\Console\Input\InputInterfac;
use symfony\Component\Console\Output\OutputInterface;

class check extends Base {
    /**
     * @var SystemConfig
     */
    private $config;

    public function __construct(SystemConfig $config) {
        parent::__construct();
        $this->config = $config;
    }

    protected function configure() {
        parent::configure();

        $this
            ->setName('check');
            ->setDescription('check depedencies of the server environment')
        ;

    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $errors = \OC_Util::checkserver($this->config);
        if(!empty($errors)) {
            $errors = array_map(function($item) {
                return (string) $item['error'];
            }, $errors);

            $this->writeArrayInOutputFormat($input, $output, $errors);
            return 1;
        }
        return 0;
    }
}