<?php

final class ArgParser {

    const OPTION_REGEX = '/^(?:-([a-zA-Z0-9])|--([a-zA-Z0-9][a-zA-Z0-9\-]+))(?:=(.*))?$/';

    private $options;

    public function __construct($options) {
        $this->options = $options;
    }

    public function parseArguments() {
        $arguments = $this->getOpt();
        $this->validateArguments($arguments);
        return $this->mergeArguments($arguments);
    }

    private function getShortOpts() {
        $shortOpts = array();

        foreach($this->options as $longOpt => $shortOpt) {
            $shortOpts[] = $shortOpt;
        }

        return $shortOpts;
    }

    private function getLongOpts() {
        $longOpts = array();

        foreach($this->options as $longOpt => $shortOpt) {
            $longOpts[] = $longOpt;
        }

        return $longOpts;
    }

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
                throw new InvalidArgumentException('Invalid argument "'.$arg.'"');
            }
        }

        return $result;
    }

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

    private function getLongOptFromShortOpt($option) {
        foreach ($this->options as $longOpt => $shortOpt) {
            if ($this->stripColons($shortOpt) == $option)
                return $this->stripColons($longOpt);
        }

        return null;
    }

    private function stripColons($option) {
        return trim($option, ':');
    }

    private function validateArguments(array $arguments) {
        $validOptions = $this->getLongOpts();
        $validOptions = array_merge($validOptions, $this->getShortOpts());
        $validOptions = array_map([$this, 'stripColons'], $validOptions);


        foreach ($arguments as $arg => $value) {
            if (!in_array($arg, $validOptions))
                throw new InvalidArgumentException('Invalid argument "'.$arg.'"');
            if (!$this->isArgValueValid($arg, $value))
                throw new InvalidArgumentException('Invalid argument "'.$arg.'"');
        }
    }

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
}