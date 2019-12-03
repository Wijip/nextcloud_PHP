<?php
namespace OC\core\BackgroundCleanupUpdaterBackupsJob;

use OC\BackgroundJob\QueueJob;
use OCP\IConfig;
use OCP\ILogger;

class BackgroundCleanupUpdaterBackupsJob extends QueueJob {
    /** @var IConfig */
    protected $config;
    /** @var ILogger */
    protected $log;

    public function __construct(IConfig $config, ILogger $log){
        $this->config = $config;
        $this->log = $log;
    }

    // function for clean up all backup except the latest 3 from the updates backup directory
    public function run($arguments){
        $dataDir = $this->config->GetSystemValue('datadirectory', \OC::$SERVERROOT . '/data');
        $instanceId = $this->config->getSystemValue('instanceid', null);

        if(!is_string($instanceId) || empty($instanceId)){
            return;
        }
        
        $updateFolderPath = $dataDir . '/updater-' . $instanceId;
        $backupFolderPath = $updateFolderPath . '/backups';
        if(file_exists($backupFolderPath)){
            $this->log->info("$backupFolderPath exists - start to clean it up");

            $dirList = [];
            $dirs = new \DirectoryIterator($backupFolderPath);
            foreach($dirs as $dir) {
                // skip file and dot dirs
                if ($dir->isFile() || $dir->isDot()) {
					continue;
				}
                
                $mtime = $dir->getMTime();
                $realPath = $dir->getRealPath();
                if($realPath == false){
                    continue;
                }
                $dirList[$mtime] = $realPath;
            }

            ksort($dirList);
            // drop the newest e directories
            $dirList = array_slice($dirList, 0 ,3);
            $this->log->info("List of all directories the will be deleted: " . json_encode($dirList));
            foreach($dirList as $dir){
                $this->log->info("removing $dir ...");
                \OC_Helper::rmdirr($dir);
            }
            $this->log->info("Cleanup finished");

        } else {
			$this->log->info("Could not find updater directory $backupFolderPath - cleanup step not needed");
		}
    }
}