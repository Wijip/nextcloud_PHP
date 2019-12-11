<?php
namespace OC\Core\Command\Config\App;

use OCP\Iconfig;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;

abstract class Base extends \OC\Core\Command\Base {
    /** @var IConfig */
    protected $config;
    /**
     * @param string $argumentName
     * @param CompletionContext $context
     * @return string[]
     */
    public function completeArgumentValues($argumentName, CompletionContext $context) {
        if($argumentName === 'app') {
            return \OC_App::getAllApps();
        }
        if($argumentName === 'name') {
            $appName = $context->getWordAtIndex($context->getWordIndex() -1);
            return $this->config->getAppKeys($appName);
        }
        return [];
    }
}