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
        $this->results = array();
    }

    /**
     * Add test result to output
     *
     * @param TestResult $result
     */
    public function addTestResult(TestResult $result)
    {
        $dirs = explode('/', $result->getDirectory());
        $dirs = array_filter($dirs);

        $subdir = &$this->results;

        foreach ($dirs as $dir) {
            $subdir = &$subdir[$dir];
        }

        $subdir[] = $result;
    }

    /**
     * Render output to STDOUT
     */
    public function renderOutput()
    {
        var_dump($this->results);
    }
}