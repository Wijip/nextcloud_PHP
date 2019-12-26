<?php
namespace OC\Core\Command\L10n;

use DirectoryIterator;

use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

class CreateJs extends Command implements CompletionAwareInterface {
    protected function configure() {
        $this
            ->setName('l10n:createjs')
            ->setDescription('create javaScript translation files for a given app')
            ->setArgument(
                'app',
                InputOption::VALUE_REQUIRED,
                'name of the language'
            );
    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        $app = $input->getArgument('app');
        $lang = $input->getArgument('lang');
        $path = \OC_App::getAppPath($app);
        if($path === false) {
            $output->writeln("the app <$app> is unknow.");
            return;
        }
        $languages = $lang;
        if(empty($lang)) {
            $languages = $this->getAllLanguages($path);
        }
        foreach($languages as $lang) {
            $this->writeln($app, $path, $lang, $output);
        }
    }
    private function getAllLanguages($path) {
        $result = array();
        foreach (new DirectoryIterator("$path/l10n") as $fileInfo) {
            if($fileInfo->isDot()) {
                continue;
            }
            if($fileInfo->isDir()) {
                continue;
            }
            if($fileInfo->getExtension() !== 'php') {
                continue;
            }
            $result[] = substr($fileInfo->getBasename(), 0, -4);
        }
        return $result;
    }
    private function writeFiles($app, $path, $lang, OutputInterface $output) {
        list($translation, $plurals) = $this->loadTranslations($path, $lang);
        $this->writeJsFile($app, $path, $lang, $output, $translation, $plurals);
        $this->writeJsonFile($path, $lang, $output, $translation, $plurals);
    }

    private function writeJsFile($app, $path, $lang, OutputInterface $output, $translation, $plurals) {
        $jsFile = "$path/l10n/$lang.js";
        if(file_exists($jsFile)) {
            $output->writeln("File already existx : $jsFile");
            return;
        }
        $content = "OC.L10N.register(\n     \"$app\",\n {\n ";
        $jsTrans = array();
        foreach ($translation as $id => $val) {
            if(is_array($val)) {
                $val = '[ ' . implode(',',$val). ' ]';
            }
            $jsTrans[] = "\"$id\" : \"$val\"";
        }
        $content .= implode(",\n    ", $jsTrans);
        $content .= "\n},\n\"$plurals\");\n";
        file_put_contents($jsFile, $content);
        $output->writeln("Javascript translation file generated: $jsFile");
    }
    private function writeJsonFile($path, $lang, OutputInterface $output, $translation, $plurals) {
        $jsFile = "$path/l10n/$lang.json";
        if(file_exists($jsFile)) {
            $output->writeln("File already exists: $jsFile");
            return;
        }
        $content = array('translation' => $translation, 'pluralFrom' => $plurals);
        file_put_contents($jsFile, json_encode($content));
        $output->writeln("Json translation file generated: $jsFile");
    }
    private function loadTranslations($path, $lang) {
        $phpFile = "$path/l10n/$lang.php";
        $TRANSLATIONS = array();
        $PLURAL_FROMS = '';
        if(!file_exists($phpFile)) {
            throw new UnexpectedValueException("PHP translation file <$phpFile> does not exists.");
        }
        require $phpFile;
        return array($TRANSLATIONS, $PLURAL_FROMS);
    }
    /**
     * @param string $optionName
     * @param CompletionContext $context
     * @return string[]
     */
    public function completeOptionValues($optionName, CompletionContext $context) {
        return [];
    }
    /**
     * @param string $argumentName
     * @param CompletionContext $context
     * @return string[]
     */
    public function completeArgumentValues($argumentName, CompletionContext $context) {
        if($argumentName === 'app') {
            return \OC_App::getAllApps();
        } else if ($argumentName === 'lang') {
            $appName = $context->getWordAtIndex($context->getWordIndex() - 1);
            return $this->getAllLanguages(\OC_App::getAppPath($appName));
        }
        return [];
    }
}