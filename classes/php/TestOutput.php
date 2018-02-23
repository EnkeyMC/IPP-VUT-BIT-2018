<?php

namespace TestSuite;


abstract class TestOutput
{
    public abstract function addTestResult(TestResult $result);
    public abstract function renderOutput();
}