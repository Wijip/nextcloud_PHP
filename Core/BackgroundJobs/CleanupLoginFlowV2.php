<?php
declare(strict_types=1);

namespace OC\Core\BackgroundJobs;

use OC\Core\Db\LoginFlowV2Mapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;

class CleanupLoginFlowV2 extends TimedJob {

	/** @var LoginFlowV2Mapper */
	private $loginFlowV2Mapper;

	public function __construct(ITimeFactory $time, LoginFlowV2Mapper $loginFlowV2Mapper) {
		parent::__construct($time);
		$this->loginFlowV2Mapper = $loginFlowV2Mapper;

		$this->setInterval(3600);
	}

	protected function run($argument) {
		$this->loginFlowV2Mapper->cleanup();
	}
}
