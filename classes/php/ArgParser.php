<?php

/**
 * Class ArgParser
 *
 * Parses command line arguments
 */
final class ArgParser {

    /** Regular expresion used for option parsing */
    const OPTION_REGEX = '/^(?:-([a-zA-Z0-9])|--([a-zA-Z0-9][a-zA-Z0-9\-]+))(?:=(.*))?$/';

    /** @var  array Valid options with long => short mapping */
    private $options;

    /**
     * ArgParser constructor.
     * @param $options array Valid options with long => short mapping
     */
    public function __construct($options) {
        $this->options = $options;
    }

    /**
     * Parse command line arguments
     *
     * @return array Parsed arguments
     */
    public function parseArguments() {
        $arguments = $this->getOpt();
        $this->validateArguments($arguments);
        return $this->mergeArguments($arguments);
    }

    /**
     * Get short options
     *
     * @return array short options
     */
    private function getShortOpts() {
        $shortOpts = array();

        foreach($this->options as $longOpt => $shortOpt) {
            $shortOpts[] = $shortOpt;
        }

        return $shortOpts;
    }

    /**
     * Get long options
     *
     * @return array long options
     */
    private function getLongOpts() {
        $longOpts = array();

        foreach($this->options as $longOpt => $shortOpt) {
            $longOpts[] = $longOpt;
        }

        return $longOpts;
    }

    /**
     * Get command line arguments
     *
     * @return array command line arguments
     */
    private function getOpt() {
        global $argv;
        unset($argv[0]);  // Remove script
        $result = array();
        $match = array();

        foreach ($argv as $arg) {
            if (preg_match(self::OPTION_REGEX, $arg, $match)) {
                unset($match[0]);
                $match = array_filter($match);
                $option = false;
                foreach($match as $group) {
                    if ($option)
                        $result[$option] = $group;
                    else {
                        $result[$group] = false;
                        $option = $group;
                    }
                }
            } else {
                throw $this->getArgumentException($arg);
            }
        }

        return $result;
    }

    /**
     * Merge short forms into long option forms
     *
     * @param array $arguments command line arguments
     * @return array Merged arguments
     */
    private function mergeArguments(array $arguments) {
        $mergedArguments = array();

        foreach($arguments as $argument => $value) {
            $longOpt = $this->getLongOptFromShortOpt($argument);

            if ($longOpt === null) {
                $mergedArguments[$argument] = $value;
            } else {
                $mergedArguments[$longOpt] = $value;
            }
        }

        return $mergedArguments;
    }

    /**
     * Get long version of short option if possible, otherwise null
     *
     * @param $option string short or long option
     * @return null|string long option
     */
    private function getLongOptFromShortOpt($option) {
        foreach ($this->options as $longOpt => $shortOpt) {
            if ($this->stripColons($shortOpt) == $option)
                return $this->stripColons($longOpt);
        }

        return null;
    }

    /**
     * Remove leading or trailing colons from option
     *
     * @param $option string
     * @return string option without leading or trailing colons
     */
    private function stripColons($option) {
        return trim($option, ':');
    }

    /**
     * Check if parsed arguments are allowed or if they have required values
     *
     * @param array $arguments command line arguments
     */
    private function validateArguments(array $arguments) {
        $validOptions = $this->getLongOpts();
        $validOptions = array_merge($validOptions, $this->getShortOpts());
        $validOptions = array_map([$this, 'stripColons'], $validOptions);


        foreach ($arguments as $arg => $value) {
            if (!in_array($arg, $validOptions))
                throw $this->getArgumentException($arg);
            if (!$this->isArgValueValid($arg, $value))
                throw $this->getArgumentException($arg);
        }
    }

    /**
     * Check if argument has required value or no value if value is forbidden
     *
     * @param $arg string argument
     * @param $value string argument value
     * @return bool true if valid, false otherwise
     */
    private function isArgValueValid($arg, $value) {
        foreach ($this->options as $long => $short) {
            if ($this->stripColons($long) === $arg || $this->stripColons($short) === $arg) {
                $colons = strstr($long, ':');
                if ($colons === ':') {
                    return $value !== false;
                } else if ($colons === '::') {
                    return true;
                } else {
                    return $value === false;
                }
            }
        }
        return false;
    }

    /**
     * Get argument exception with message
     *
     * @param $arg string argument
     * @return InvalidArgumentException
     */
    private function getArgumentException($arg) {
        return new InvalidArgumentException('Invalid argument "'.$arg.'"', ExitCodes::ERROR_PARAMETER);
    }
}