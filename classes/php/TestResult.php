<?php

namespace TestSuite;


class TestResult
{
    const ERROR_PARSE_RETURN_CODE = 0;
    const ERROR_INT_RETURN_CODE = 1;
    const ERROR_OUT_DIFF = 2;

    private $name;
    private $error;

    public function __construct($testName)
    {
        $this->name = $testName;
        $this->error = null;
    }

    public function error($type, $details) {
        $this->error = ['type' => $type, 'details' => $details];
    }

    public function hasError() {
        return $this->error !== null;
    }

    public function getError() {
        return $this->error;
    }

    public function getName() {
        return $this->name;
    }
}