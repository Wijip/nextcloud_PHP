<?php
declare(strict_types=1);
namespace OC\Core\Data;

class LoginFlowV2Tokens {
    /** @var string */
    private $loginToken;
    /** @var string */
    private $pollToken;

    public function __construct(string $loginToken, string $pollToken) {
        $this->loginToken = $loginToken;
        $this->pollToken = $pollToken;
    }

    public function getPollToken(): string {
        return $this->pollToken;
    }

    public function getLoginToken(): string {
        return $this->loginToken;
    }
}