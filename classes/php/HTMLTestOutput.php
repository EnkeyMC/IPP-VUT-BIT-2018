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
        $result = $this->recursiveResultCalc($this->results);
        include 'template.php';
    }

    private function recursiveResultCalc(array $results, $dir='.') {
        $calcData = [
            'dir' => $dir,
            'success_count' => 0,
            'total_count' => 0,
            'failed_tests' => [],
            'subdirs' => []
        ];
        foreach ($results as $dir => $result) {
            if ($result instanceof TestResult) {
                $calcData['total_count']++;
                if ($result->hasError()) {
                    $failedTest = [
                        'name' => $result->getName(),
                        'details' => $this->getTestErrorMsg($result)
                        ];
                    $calcData['failed_tests'][] = $failedTest;
                } else {
                    $calcData['success_count']++;
                }
            } else {
                $subdir = $this->recursiveResultCalc($result, $dir);
                $calcData['subdirs'][] = $subdir;
                $calcData['success_count'] += $subdir['success_count'];
                $calcData['total_count'] += $subdir['total_count'];
            }
        }

        return $calcData;
    }

    private function getTestErrorMsg(TestResult $testResult) {
        if (!$testResult->hasError())
            return '';

        $error = $testResult->getError();

        switch ($error['type']) {
            case TestResult::ERROR_PARSE_RETURN_CODE:
                return 'Chybný návratový kód parse skriptu.<br>'.
                    'Očekávaný: '.$error['details']['expected'].'<br>'.
                    'Skutečný: '.$error['details']['actual'];

            case TestResult::ERROR_INT_RETURN_CODE:
                return 'Chybný návratový kód interpretu.<br>'.
                    'Očekávaný: '.$error['details']['expected'].'<br>'.
                    'Skutečný: '.$error['details']['actual'];

            case TestResult::ERROR_OUT_DIFF:
                return 'Nesouhlasí výstup interpretu.<br>'.
                    str_replace(PHP_EOL, '<br>', $error['details']['diff']);
        }
    }
}