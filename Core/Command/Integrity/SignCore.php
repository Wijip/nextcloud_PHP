<?php
namespace OC\Core\Command\Integrity;

use OC\IntegrityCheck\Checker;
use OC\IntegrityCheck\Helpers\FileAccessHelper;
use phpseclib\Crypt\RSA;
use phpseclib\File\X509;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SignCore extends Command {
    /** @var Checker */
	private $checker;
	/** @var FileAccessHelper */
	private $fileAccessHelper;

	/**
	 * @param Checker $checker
	 * @param FileAccessHelper $fileAccessHelper
	 */
    public function __construct(Checker $checker, FileAccessHelper $fileAccessHelper) {
        parent::__construct(null);
        $this->checker = $checker;
        $this->fileAccessHelper = $fileAccessHelper;
    }
    protected function configure() {
		$this
			->setName('integrity:sign-core')
			->setDescription('Sign core using a private key.')
			->addOption('privateKey', null, InputOption::VALUE_REQUIRED, 'Path to private key to use for signing')
			->addOption('certificate', null, InputOption::VALUE_REQUIRED, 'Path to certificate to use for signing')
			->addOption('path', null, InputOption::VALUE_REQUIRED, 'Path of core to sign');
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        $privateKeypath = $input->getOption('privateKey');
        $keyBundlePath = $input->getOption('certificate');
        $path = $input->getoption('path');
        If(is_null($privateKeypath) || is_null($keyBundlePath) || is_null($path)) {
            $output->writeln('--privateKey, -- certificate and --path are required.');
            return null;
        }
        $privateKey = $this->fileAccesshelper->file_get_contents($privateKeypath);
        $keyBundle = $this->fileAccessHelper->file_get_contents($keyBundlePath);

        if($privateKey === false) {
            $output->writeln(sprintf('private key "%s" does not exists'));
            return null;
        }
        if($keyBundle === false) {
            $output->writeln(sprintf('certification "%s" does not exists,' ,$keyBundlePath));
            return null;
        }

        $rsa = new RSA();
        $rsa->loadKey($privateKey);
        $x509 = new X509();
        $x509->loadX509($keyBundle);
        $x509->setPrivateKey($rsa);

        try{
            $this->checker->writeCoreSignature($x509, $rsa, $path);
            $output->writeln('Successfully signed "core"');
        }catch (\Exception $e) {
            $output->writeln('Error: ' .$e->getMessage());
            return 1;
        }
        return 0;
    }
}