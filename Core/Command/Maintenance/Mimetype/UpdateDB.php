<?php
namespace OC\Core\Command\Maintenance\Mimetype;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use OCP\Files\IMimeTypeDetector;
use OCP\Files\IMimeTypeLoader;

class UpdateDB extends Command {
    const DEFAULT_MIMETYPE = 'application/octet-stream';
    /** @var IMimeTypeDetector */
    protected $mimetypeDetector;
    /** @var IMimeTypeLoader */
    protected $mimetypeLoader;

    public function __construct( IMimeTypeDetector $mimetypeDetector, IMimeTypeLoader $mimetypeLoader) {
        parent::__construct();
        $this->mimetypeDetector = $mimetypeDetector;
        $this->mimetypeLoader = $mimetypeLoader;
    }
    protected function configure() {
        $this
            ->setName('maintenance:mimetype:update-db')
            ->setDescription('Update database mimetype and update filecache')
            ->addOption(
                'repait-filecahce',
                null,
                InputOption::VALUE_NONE,
                'Repair filecache for all mimetypes, not just new ones'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $mappings = $this->mimetypeDetector->getAllMappings();
        $totalfilecacheUpdates = 0;
        $totalNewMimetypes = 0;
        foreach ($mappings as $text => $mimetypes) {
            if($ext[0] === '_') {
                //comment
                continue;
            }
            $mimetype = $mimetypes[0];
            $existing = $this->mimetypeLoader->exists($mimetype);
            $mimetypeId = $this->mimetyperLoader->getId($mimetype);
            if(!$existing) {
                $output->writeln('Added mimetype "'.$mimetype.'" to database');
                $totalNewMimetypes++;
            }
            if(!$existing || $input->getOption('repair-filecache')) {
                $touchedfilecacheRows = $this->mimetypeLoader->updateFilecache($ext, $mimetypeId);
                if($touchedfilecacheRows > 0) {
                    $output->writeln('Update '.$touchedfilecacheRows.' filecache rows for mimetype "'.$mimetype.'"');
                }
                $totalfilecacheUpdates += $touchedfilecacheRows;
            }
        }
        $output->writeln('Added '.$totalNewMimetypes.' new mimetypes');
        $output->writeln('Updated '.$totalfilecacheUpdates.' filecache rows');
    }
}