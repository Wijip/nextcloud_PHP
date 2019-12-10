<?php
namespace OC\Core\Command\App;

use Throwable;
use OC\Installer;
use OCP\App\IAppManager;
use OCP\ILogger;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Remove extends Command implements CompletionAwareInterface {
    
}