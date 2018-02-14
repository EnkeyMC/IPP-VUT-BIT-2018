<?php

/**
 * OSUtils
 *
 * Abstracts OS specific commands
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
     * @return OSUtils::OS_WIN or OSUtils::OS_UNIX
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
        return self::OS_COMMANDS[$this->os][$cmd];
    }

    public static function getInstance() {
        if (self::$instance === NULL)
            self::$instance = new OSUtils();
        return self::$instance;
    }

    public static function checkFileDifference($filename1, $filename2) {
        $output = array();
        $rc = 0;

        exec(self::buildCommand(self::CMD_DIFF, [$filename1, $filename2]), $output, $rc);

        return ['output' => $output, 'return_code' => $rc];
    }

    private static function buildCommand($cmd, array $args) {
        $cmd = self::getInstance()->getOSSpecificCommand($cmd);
        foreach ($args as $arg) {
            $cmd .= ' ' . $arg;
        }

        return $cmd;
    }
}