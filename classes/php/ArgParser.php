<?php

final class ArgParser {

    const OPTION_REGEX = '/^(?:-([a-zA-Z0-9])|--([a-zA-Z0-9]+))(?:=(.*))?$/';

    private $options;

    public function __construct($options) {
        $this->options = $options;
    }

    public function parseArguments() {
        $shortOpts = $this->getShortOpts();
        $longOpts = $this->getLongOpts();

        $arguments = $this->getOpt($shortOpts, $longOpts);

        return $this->mergeArguments($arguments);
    }

    private function getShortOpts() {
        $shortOpts = '';

        foreach($this->options as $longOpt => $shortOpt) {
            $shortOpts .= $shortOpt;
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

    private function getOpt($shortOpts, $longOpts) {

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
            if (self::stripColons($shortOpt) == $option)
                return self::stripColons($longOpt);
        }

        return null;
    }

    private static function stripColons($option) {
        return trim($option, ':');
    }
}