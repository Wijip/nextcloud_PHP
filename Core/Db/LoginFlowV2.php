<?php
declare(strict_types=1);

namespace OC\Core\Db;

use OCP\AppFramework\Db\Entity;

class LoginFlowV2 extends Entity {
    /** @var int */
	protected $timestamp;
	/** @var int */
	protected $started;
	/** @var string */
	protected $pollToken;
	/** @var string */
	protected $loginToken;
	/** @var string */
	protected $publicKey;
	/** @var string */
	protected $privateKey;
	/** @var string */
	protected $clientName;
	/** @var string */
	protected $loginName;
	/** @var string */
	protected $server;
	/** @var string */
    protected $appPassword;
    
    public function __construct() {
        $this->addType('timestamp', 'int');
		$this->addType('started', 'int');
		$this->addType('pollToken', 'string');
		$this->addType('loginToken', 'string');
		$this->addType('publicKey', 'string');
		$this->addType('privateKey', 'string');
		$this->addType('clientName', 'string');
		$this->addType('loginName', 'string');
		$this->addType('server', 'string');
		$this->addType('appPassword', 'string');
    }
}