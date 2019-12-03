<?php
declare(strict_types=1);
namespace OC\Core\Data;
class LoginFlowV2Credentials implements \JsonSerializable {
    /** @var string */
    private $server;
    /** @var string */
    private $loginName;
    /** @var string */
    private $appPassword;

    public function __construct(string $server, string $loginName, string $appPassword) {
        $this->server = $server;
        $this->loginName = $loginName;
        $this->appPassword = $appPassword;
    }
    /** 
     * @return string
     */
    public function getServer(): string{
        return $this->server;
    }
    /**
     * @return string
     */
    public function getLoginName(): string {
        return $this->loginName;
    }
    /**
     * @return string
     */
    public function getAppPassword(): string {
        return $this->appPassword;
    }

    public function jsonSerialize(): array {
        return[
            'server' => $this->server,
            'loginName' => $this->loginName,
            'appPassword' => $this->appPassword
        ];
    }
    
}