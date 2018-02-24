<?php

/**
 * OSUtils
 *
 * Abstracts OS functions
 */
final class OSUtils {
    const OS_WIN = 'Windows';
    const OS_UNIX = 'Unix';

    const CMD_DIFF = 'diff';

    /**
     * OS Command mapping
     */
    const OS_COMMANDS = array(
        self::OS_WIN => [
            self::CMD_DIFF => 'FC'
        ],
        self::OS_UNIX => [
            self::CMD_DIFF => 'diff'
        ]
    );

    private static $instance = null;
    /** @var string Operating system name */
    private $os;

    /**
     * Singleton constructor
     */
    private function __construct() {
        $this->os = $this->determineOsType();
    }

    /**
     * Determine which OS PHP is running on
     *
     * @return string OSUtils::OS_WIN or OSUtils::OS_UNIX
     */
    private function determineOsType() {
        // Not very good determination, but it'll do
        if (strpos(php_uname('s'), self::OS_WIN) !== false) {
            return self::OS_WIN;
        } else {
            return self::OS_UNIX;
        }
    }

    private function getOSSpecificCommand($cmd) {
        if (array_key_exists($cmd, self::OS_COMMANDS[$this->os]))
            return self::OS_COMMANDS[$this->os][$cmd];
        else
            return $cmd;
    }

    public static function getInstance() {
        if (self::$instance === NULL)
            self::$instance = new OSUtils();
        return self::$instance;
    }

    public static function checkFileDifference($filename1, $filename2) {
        return self::runCommand(self::CMD_DIFF, [$filename1, $filename2]);
    }

    private static function buildCommand($cmd, array $args, $inputRedir='', $outputRedir='') {
        $cmd = self::getInstance()->getOSSpecificCommand($cmd);
        foreach ($args as $arg) {
            $cmd .= ' ' . $arg;
        }

        if ($inputRedir)
            $cmd .= ' < '.$inputRedir;
        if ($outputRedir)
            $cmd .= ' > '.$outputRedir;

        return $cmd;
    }

    public static function getFilesInDirByRegex($directory, $regex, $recursive=false) {
        if ($recursive) {
            $directoryIterator = new RecursiveDirectoryIterator($directory);
            $iterator = new RecursiveIteratorIterator($directoryIterator);
        } else {
            $directoryIterator = new DirectoryIterator($directory);
            $iterator = new IteratorIterator($directoryIterator);
        }

        $regexIterator = new RegexIterator($iterator, $regex, RegexIterator::GET_MATCH);

        return $regexIterator;
    }

    public static function changeFileExtension($filePath, $newExtension) {
        $new = preg_replace('/(?<=\.)[^\.\\\\\/]+$/', $newExtension, $filePath);
        if ($newExtension === '')
            $new = rtrim($new, '.');
        return $new;
    }

    public static function runCommand($cmd, array $args, $inputRedir='', $outputRedir='') {
        $output = array();
        $rc = 0;

        exec(self::buildCommand($cmd, $args, $inputRedir, $outputRedir), $output, $rc);

        return ['output' => $output, 'return_code' => $rc];
    }

    public static function normalizePath($path) {
        return preg_replace('/\\\\/', '/', $path);
    }
}