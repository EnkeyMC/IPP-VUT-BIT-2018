<?php

namespace TestSuite;

class HTMLTestOutput implements TestOutput {

    /** @var array TestResult categorized by directories */
    private $results;

    /**
     * HTMLTestOutput constructor.
     */
    public function __construct()
    {
        $this->results = [
            'results' => array(),
            'subdirs' => array()
        ];
    }

    /**
     * Add test result to output
     *
     * @param TestResult $result
     */
    public function addTestResult(TestResult $result)
    {
        $dirs = explode('/', $result->getDirectory());

        $subdir = &$this->results;

        foreach ($dirs as $dir) {
            if (!isset($subdir['subdirs'][$dir])) {
                $subdir[$dir] = [
                    'results' => array(),
                    'subdirs' => array()
                ];
            }


        }
    }

    private function createSubdir(&$dir) {
    }

    /**
     * Render output to STDOUT
     */
    public function renderOutput()
    {
        var_dump($this->results);
    }
}