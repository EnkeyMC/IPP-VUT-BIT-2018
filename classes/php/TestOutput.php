<?php

namespace TestSuite;

/**
 * Class TestOutput
 * @package TestSuite
 *
 * Interface for displaying test output
 */
interface TestOutput
{
    /**
     * Add test result to output
     *
     * @param TestResult $result
     */
    public function addTestResult(TestResult $result);

    /**
     * Render output to STDOUT
     */
    public function renderOutput();
}