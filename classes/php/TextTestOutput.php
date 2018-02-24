<?php

namespace TestSuite;


class TextTestOutput extends TestOutput
{
    private $results;
    private $successCount;

    public function __construct()
    {
        $this->results = array();
    }

    public function addTestResult(TestResult $result)
    {
        $this->results[] = $result;
        if (!$result->hasError())
            $this->successCount++;
    }

    public function renderOutput() {
        foreach ($this->results as $result) {
            if ($result->hasError()) {
                $error = $result->getError();

                echo $result->getName().':'.PHP_EOL;
                switch ($error['type']) {
                    case TestResult::ERROR_PARSE_RETURN_CODE:
                        echo 'Unexpected parse return code'.PHP_EOL;
                        echo 'Expected: '.$error['details']['expected'].PHP_EOL;
                        echo 'Actual: '.$error['details']['actual'].PHP_EOL;
                        echo PHP_EOL;
                        break;
                    case TestResult::ERROR_INT_RETURN_CODE:
                        echo 'Unexpected interpret return code'.PHP_EOL;
                        echo 'Expected: '.$error['details']['expected'].PHP_EOL;
                        echo 'Actual: '.$error['details']['actual'].PHP_EOL;
                        echo PHP_EOL;
                        break;
                    case TestResult::ERROR_OUT_DIFF:
                        echo 'Different interpret output'.PHP_EOL;
                        echo $error['details']['diff'].PHP_EOL;
                        echo PHP_EOL;
                        break;
                }
            }
        }
        echo $this->successCount.'/'.sizeof($this->results).' Successful tests.';
    }
}