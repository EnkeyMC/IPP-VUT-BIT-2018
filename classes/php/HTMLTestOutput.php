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

    /**
     * Recursively calculate result summary
     *
     * @param array $results test results and subdirectories
     * @param string $curr_dir current test directory name ('' by default)
     *
     * @return array calculated data
     */
    private function recursiveResultCalc(array $results, $curr_dir='') {
        $calcData = [
            'dir' => $curr_dir,
            'dir_id' => $this->get_unique_id(),
            'success_count' => 0,
            'total_count' => 0,
            'test_info' => [],
            'subdirs' => []
        ];
        foreach ($results as $dir => $result) {
            if ($result instanceof TestResult) {
                $calcData['total_count']++;
                if ($result->hasError()) {
                    $failedTest = [
                        'success' => false,
                        'name' => $result->getName(),
                        'details' => $this->getTestErrorMsg($result)
                        ];
                    $calcData['test_info'][] = $failedTest;
                } else {
                    $calcData['success_count']++;
                    $calcData['test_info'][] = [
                        'success' => true,
                        'name' => $result->getName(),
                        'details' => 'Úspěšný'
                    ];
                }
            } else {
                $subdir = $this->recursiveResultCalc($result, $curr_dir.'/'.$dir);
                $calcData['subdirs'][] = $subdir;
                $calcData['success_count'] += $subdir['success_count'];
                $calcData['total_count'] += $subdir['total_count'];
            }
        }

        return $calcData;
    }

    private function get_unique_id() {
        static $id = 0;
        return 'ID'.$id++;
    }

    /**
     * Get error message based on test result
     *
     * @param TestResult $testResult
     * @return string error message
     */
    private function getTestErrorMsg(TestResult $testResult) {
        if (!$testResult->hasError())
            return '';

        $error = $testResult->getError();

        switch ($error['type']) {
            case TestResult::ERROR_PARSE_RETURN_CODE:
                return 'Chybný návratový kód parse skriptu.<br>'.
                    'Očekávaný: '.$error['details']['expected'].'<br>'.
                    'Skutečný: '.$error['details']['actual'].'<br><br>'.
                    'STDERR:<br>'.htmlspecialchars($error['stderr']);

            case TestResult::ERROR_INT_RETURN_CODE:
                return 'Chybný návratový kód interpretu.<br>'.
                    'Očekávaný: '.$error['details']['expected'].'<br>'.
                    'Skutečný: '.$error['details']['actual'].'<br><br>'.
                    'STDERR:<br>'.htmlspecialchars($error['stderr']);

            case TestResult::ERROR_OUT_DIFF:
                return 'Nesouhlasí výstup interpretu.<br><br>'.
                    str_replace(PHP_EOL, '<br>', htmlspecialchars($error['details']['diff'])).'<br><br>'.
                    'STDERR:<br>'.htmlspecialchars($error['stderr']);
        }

        return '';
    }
}