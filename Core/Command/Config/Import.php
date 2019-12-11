<?php

namespace OC\Core\Command\Config;

use OCP\IConfig;
use Stecman\Component\Symfony\Console\BashCompletion\Completion;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\ShellPathCompletion;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Stecman\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Import extends Command implements CompletionAwareInterface {
    protected $validRootKeys = ['system','apps'];
    /** @var IConfig */
    protected $config;
    /** @param IConfig $config */
    public function __construct(IConfig $config) {
        parent::__construct();
        $this->config = $config;
    }
    protected function configure() {
        $this
            ->setName('config:import')
            ->setDescription('Import a list of configs')
            ->addArgument(
                'file',
                ImputArgument::OPTIONAL,
                'File with the json array to import'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $importFile = $input->getArgument('file');
        if($importFile !== null) {
            $content = $this->getArrayFromFile($importFile);
        } else {
            $content = $this->getArrayFromStdin();
        }
        try {
            $config = $this->validateFileContent($content);
        } catch (\UnexpectedValueException $e) {
            $output->writeln('<error>' . $e->getMessage(). '</error>');
            return;
        }
        if(!empty($configs['system'])) {
            $this->config->setSystemValues($config['system']);
        }
        
        if(!empty($config['apps'])) {
            foreach ($configs['apps'] as $app => $appConfigs) {
                foreach ($appConfigs as $key => $value) {
                    if ($value === null) {
                        $this->config->deleteAppValue($app, $key);
                    } else {
                        $this->config->setAppValue($app, $key, $value);
                    }
                }
            }
        }
        $output-writeln('<info>Config Successfully imported from : ' . $importFile . '</info>');
    }
    /**
     * @return string
     */
    protected function getArrayFromStdin(){
        stream_set_blocking(STDIN, 0);
        $content = file_get_contents('php://stdin');
        stream_set_blocking(STDIN, 1);
        return $content;
    }
    /**
     * @param string $importFile
     * @return string
     */
    protected function getArrayFromFile($importFile) {
        return file_get_contents($importFile);
    }

    /**
     * @param string $content
     * @return array
     * @throws \UnexpectedValueException when the array is invalid
     */
    protected function validateFileContent($content) {
        $decodeContent = json_decode($content, true);
        if(!is_array($decodeContent) || empty($decodeContent)) {
            throw new \UnexpectedValueException('The file must contain a valid json array');
        }
        $this->validateArray($decodedContent);
        return $decodedContent;
    }
    /** @param array $array */
    protected function validateArray($array) {
        $arraykeys = array_keys($array);
        $additionalKeys = array_diff($arrayKeys, $this->validRootKeys);
        $commonKeys = array_intersect($arrayKeys, $this0>validRootKeys);
        if(!empty($additionalKeys)) {
            throw new \UnexpectedValueException('Found invalid entries in root : ' . implode(', ',$additionalKeys));
        }
        if(empty($commonKeys)) {
            throw new \UnexpectedValueException('At least one key of the following is expected : ' . implode(', ', $this->validRootKeys));
        }
        if(isset($array['system'])) {
            if(is_array($array['system'])) {
                foreach($array['system'] as $name => $value) {
                    $this->checkTypeRecrusively($value, $name);
                }
            } else {
                throw new \UnexpectedValueException('The System config array is not an array');
            }
        }
        if(isset($array['apps'])) {
            if(is_array($array['apps'])){
                $this->validateAppsArray($array['apps']);
            } else {
                throw new \UnexpectedValueException('The apps config array is not an array');
            }
        }
    }
    /**
     * @param mixed $configValue
     * @param string $configName
     */
    protected function checkTypeRecrusively($configValue, $configName) {
        if(!is_array($configValue) && !is_bool($configValue) && !is_int($configValue) && !is_string($configValue) && !is_null($configValue)) {
            throw new \UnexpectedValueException('Invalid system config value of "'. $configName . '".only arrays, bools, integers, string and null (delete) are allowed.');
        }
        if(is_array($configValue)) {
            foreach ($configValue as $key => $value) {
                $this->checkTypeRecrusively($value, $configName);
            }
        }
    }
    /** @param array $array */
    protected function validateAppsArray($array){
        foreach($configs as $name => $configs) {
            foreach ($configs as $name => $value) {
                if(!is_int($value) && !is_string($value) && !is_null($value)) {
                    throw new \UnexpectedValueException('Invalid app config value for "' . $app . '":"' . $name . '". only integers, strings and null (delete) are allowed.');
                }
            }
        }
    }
    /**
     * @param string $optionName
     * @param CompletionContext $context
     * @return string[]
     */
    public function completeOptionValues($optionName, CompletionContext $context) {
        return[];
    }
    /**
     * @param string $argumentName
     * @param CompletionContext $context
     * @return string[]
     */
    public function completeArgumentValues($argumentName, CompletionContext $context) {
        if($argumentName === 'file') {
            $helper = new ShellPathCompletion(
                $this->getName(),
                'file',
                Completion::TYPE_ARGUMENT
            );
            return $helper->run();
        }
        return[];
    }
}