<?php
namespace OC\Core\Command\Encryption;
use OC\Encryption\Keys\Storage;
use OC\Encryption\Util;
use OC\Files\Filesystem;
use OC\Files\View;
use OCP\IConfig;
use OCP\IUserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ChangeKeyStorageRoot extends Command {
    /** @var view */
    protected $rootView;
    /** @var IUserManager */
    protected $userManager;
    /** @var IConfig */
    protected $config;
    /** @var Util */
    protected $util;
    /** @var QuestionHelper */
    protected $questionHelper;
    /**
     * @param View $rootView
     * @param IUserManager $userManager
     * @param IConfig $config
     * @param Util $util
     * @param QuestionHelper $questionHelper
     */
    public function __construct(View $view, IUserManager $userManager, IConfig $config, Util $util, QuestionHelper $questionHelper) {
        parent::__construct();
        $this->rootView = $view;
        $this->UserManager = $userManager;
        $this->config = $config;
        $this->util = $util;
        $this->questionHelper = $questionHelper;
    }
    
    protected function configure() {
        parent::configure();
        $this
            ->setName("encryption:change-key-storage-root")
            ->setDescription('Change key storage root')
            ->addArgument(
                'newRoot',
                InputArgument::OPTIONAL,
                'new root of the key storage relative to the data folder'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $oldRoot = $this->util->getKeyStorageRoot();
        $newRoot = $input->getArgument('newRoot');
        if($newRoot === null) {
            $question = new ConfiguratoinQuestion('No storage root given. do you want to reset the key storage root to the default location? (y\n)', false);
            if(!$this->questionHelper->ask($input, $output, $question)) {
                return;
            }
            $newRoot = '';
        }

        $oldRootDescription = $oldRoot !== '' ? $oldRoot : 'default storage location';
        $newRootdescription = $newRoot !== '' ? $newRoot : 'default storefe location';
        $output->writeln("change key storage root from <info>$oldRootDescription</info> to <info>$newRootdescription</info>");
        $success = $this->moveAllKeys($oldRoot, $newRoot, $input);
        if($success) {
            $this->util->setKeyStorageRoot($newRoot);
            $output->writeln('');
            $output->writeln("key storage root successfully change to <info>$newRootdescription</info>");
        }
    }
    /**
	 * move keys to new key storage root
	 *
	 * @param string $oldRoot
	 * @param string $newRoot
	 * @param OutputInterface $output
	 * @return bool
	 * @throws \Exception
	 */
	protected function moveAllKeys($oldRoot, $newRoot, OutputInterface $output) {

		$output->writeln("Start to move keys:");

		if ($this->rootView->is_dir($oldRoot) === false) {
			$output->writeln("No old keys found: Nothing needs to be moved");
			return false;
		}

		$this->prepareNewRoot($newRoot);
		$this->moveSystemKeys($oldRoot, $newRoot);
		$this->moveUserKeys($oldRoot, $newRoot, $output);

		return true;
	}

	/**
	 * prepare new key storage
	 *
	 * @param string $newRoot
	 * @throws \Exception
	 */
	protected function prepareNewRoot($newRoot) {
		if ($this->rootView->is_dir($newRoot) === false) {
			throw new \Exception("New root folder doesn't exist. Please create the folder or check the permissions and try again.");
		}

		$result = $this->rootView->file_put_contents(
			$newRoot . '/' . Storage::KEY_STORAGE_MARKER,
			'Nextcloud will detect this folder as key storage root only if this file exists'
		);

		if (!$result) {
			throw new \Exception("Can't access the new root folder. Please check the permissions and make sure that the folder is in your data folder");
		}

	}


	/**
	 * move system key folder
	 *
	 * @param string $oldRoot
	 * @param string $newRoot
	 */
	protected function moveSystemKeys($oldRoot, $newRoot) {
		if (
			$this->rootView->is_dir($oldRoot . '/files_encryption') &&
			$this->targetExists($newRoot . '/files_encryption') === false
		) {
			$this->rootView->rename($oldRoot . '/files_encryption', $newRoot . '/files_encryption');
		}
	}


	/**
	 * setup file system for the given user
	 *
	 * @param string $uid
	 */
	protected function setupUserFS($uid) {
		\OC_Util::tearDownFS();
		\OC_Util::setupFS($uid);
	}


	/**
	 * iterate over each user and move the keys to the new storage
	 *
	 * @param string $oldRoot
	 * @param string $newRoot
	 * @param OutputInterface $output
	 */
	protected function moveUserKeys($oldRoot, $newRoot, OutputInterface $output) {

		$progress = new ProgressBar($output);
		$progress->start();


		foreach($this->userManager->getBackends() as $backend) {
			$limit = 500;
			$offset = 0;
			do {
				$users = $backend->getUsers('', $limit, $offset);
				foreach ($users as $user) {
					$progress->advance();
					$this->setupUserFS($user);
					$this->moveUserEncryptionFolder($user, $oldRoot, $newRoot);
				}
				$offset += $limit;
			} while(count($users) >= $limit);
		}
		$progress->finish();
	}

	/**
	 * move user encryption folder to new root folder
	 *
	 * @param string $user
	 * @param string $oldRoot
	 * @param string $newRoot
	 * @throws \Exception
	 */
	protected function moveUserEncryptionFolder($user, $oldRoot, $newRoot) {

		if ($this->userManager->userExists($user)) {

			$source = $oldRoot . '/' . $user . '/files_encryption';
			$target = $newRoot . '/' . $user . '/files_encryption';
			if (
				$this->rootView->is_dir($source) &&
				$this->targetExists($target) === false
			) {
				$this->prepareParentFolder($newRoot . '/' . $user);
				$this->rootView->rename($source, $target);
			}
		}
	}

	/**
	 * Make preparations to filesystem for saving a key file
	 *
	 * @param string $path relative to data/
	 */
	protected function prepareParentFolder($path) {
		$path = Filesystem::normalizePath($path);
		// If the file resides within a subdirectory, create it
		if ($this->rootView->file_exists($path) === false) {
			$sub_dirs = explode('/', ltrim($path, '/'));
			$dir = '';
			foreach ($sub_dirs as $sub_dir) {
				$dir .= '/' . $sub_dir;
				if ($this->rootView->file_exists($dir) === false) {
					$this->rootView->mkdir($dir);
				}
			}
		}
	}

	/**
	 * check if target already exists
	 *
	 * @param $path
	 * @return bool
	 * @throws \Exception
	 */
	protected function targetExists($path) {
		if ($this->rootView->file_exists($path)) {
			throw new \Exception("new folder '$path' already exists");
		}

		return false;
	}
}