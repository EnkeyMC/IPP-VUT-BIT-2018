<?php

final class ArgParser {

    private $options;

    public $mockGetoptResult = NULL;  // Just for testing


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
        if ($this->mockGetoptResult) {
            return $this->mockGetoptResult;
        }
        return getopt($shortOpts, $longOpts);
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