<?php

namespace TestSuite;

/**
 * Class TestResult
 * @package TestSuite
 *
 * Holds test result information
 */
class TestResult
{
    // Test error types constants
    const ERROR_PARSE_RETURN_CODE = 0;
    const ERROR_INT_RETURN_CODE = 1;
    const ERROR_OUT_DIFF = 2;

    /** @var  string Test name (with subdirectories) */
    private $name;
    /** @var null|array Error type and details */
    private $error;

    /**
     * TestResult constructor.
     * @param $testName string test name (e.g. simple/empty_file)
     */
    public function __construct($testName)
    {
        $this->name = $testName;
        $this->error = null;
    }

    /**
     * Set test error
     *
     * @param $type int test error type
     * @param $details string|array details about error depending on $type
     */
    public function error($type, $details) {
        $this->error = ['type' => $type, 'details' => $details];
    }

    /**
     * @return bool true if result has error, false otherwise
     */
    public function hasError() {
        return $this->error !== null;
    }

    /**
     * Get test error information
     *
     * @return array|null error type and details if error occurred
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Get full test name
     *
     * @return string name
     */
    public function getFullName() {
        return $this->name;
    }

    /**
     * Get test name
     */
    public function getName() {
        $levels = explode('/', $this->name);
        return end($levels);
    }

    /**
     * Get directory this test is in
     *
     * @return string directory
     */
    public function getDirectory() {
        return \OSUtils::getDirectory($this->name);
    }
}