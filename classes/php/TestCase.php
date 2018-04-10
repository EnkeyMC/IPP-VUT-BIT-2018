<?php

namespace TestSuite;

/**
 * Class TestCase
 * @package TestSuite
 */
class TestCase
{
    // File types
    const FILE_SRC = 0;
    const FILE_IN = 1;
    const FILE_OUT = 2;
    const FILE_RC = 3;

    /** Information about files (extension and default content) */
    const FILE_INFO = [
        self::FILE_SRC => ['extension' => 'src', 'default' => ''],
        self::FILE_IN => ['extension' => 'in', 'default' => ''],
        self::FILE_OUT => ['extension' => 'out', 'default' => ''],
        self::FILE_RC => ['extension' => 'rc', 'default' => \ExitCodes::SUCCESS]
    ];

    /** @var array of file path for each file type */
    private $filePaths = [
        self::FILE_SRC => null,
        self::FILE_IN => null,
        self::FILE_OUT => null,
        self::FILE_RC => null
    ];

    /** @var TestResult */
    private $result;
    /** @var bool whether test is finished or not */
    private $finished;

    /**
     * TestCase constructor.
     * @param $srcFilePath string path to source file
     */
    public function __construct($srcFilePath)
    {
        $this->filePaths[self::FILE_SRC] = $srcFilePath;
        $this->findReferenceFiles();
        $this->generateMissingFiles();
        $this->finished = false;

        $this->result = new TestResult($this->getName());
    }

    /**
     * @return string test name
     */
    public function getName() {
        $app = TesterApp::getInstance();
        $name = str_replace(
            \OSUtils::normalizePath($app->getConfig('directory')),
            '',
            \OSUtils::normalizePath($this->filePaths[self::FILE_SRC])
        );

        $name = \OSUtils::changeFileExtension($name, '');
        return $name;
    }

    /**
     * Run test
     *
     * @return TestResult the result of the test
     */
    public function run() {
        $parseOutputFile = $this->testParse();
        if (!$this->result->hasError() && !$this->finished)
            $this->testInterpret($parseOutputFile);
        unlink($parseOutputFile);
        return $this->result;
    }

    /**
     * Test parse.php script
     *
     * @return string file path with parser output
     */
    private function testParse() {
        $app = TesterApp::getInstance();
        $tmpFlie = $this->getTmpFileName();
        $errorTmpFile = $this->getTmpFileName();
        $result = \OSUtils::runCommand(
            $app->getConfig('php-int'),
            [$app->getConfig('parse-script')],
            '"'.$this->filePaths[self::FILE_SRC].'"',
            $tmpFlie,
            $errorTmpFile
        );

        if ($result['return_code'] != \ExitCodes::SUCCESS) {
            $expectedRC = $this->getReturnCode();
            if ($expectedRC != $result['return_code']) {
                $this->result->error(
                    TestResult::ERROR_PARSE_RETURN_CODE,
                    ['expected' => $expectedRC, 'actual' => $result['return_code']],
                    file_get_contents($errorTmpFile)
                );
            }

            $this->finished = true;
        }
        unlink($errorTmpFile);

        return $tmpFlie;
    }

    /**
     * Test IPPcode18 interpret
     *
     * @param $sourceFile string file path with XML representation of IPPcode18
     */
    private function testInterpret($sourceFile) {
        $app = TesterApp::getInstance();
        $tmpFile = $this->getTmpFileName();
        $errorTmpFile = $this->getTmpFileName();
        $result = \OSUtils::runCommand(
            $app->getConfig('py-int'),
            [$app->getConfig('int-script'), '--source="'.$sourceFile.'"'],
            '"'.$this->filePaths[self::FILE_IN].'"',
            $tmpFile,
            $errorTmpFile
        );

        $expectedRC = $this->getReturnCode();
        if ($expectedRC != $result['return_code'])
            $this->result->error(
                TestResult::ERROR_INT_RETURN_CODE,
                ['expected' => $expectedRC, 'actual' => $result['return_code']],
                file_get_contents($errorTmpFile)
            );

        if ($expectedRC == \ExitCodes::SUCCESS) {
            $result = \OSUtils::checkFileDifference('"'.$this->filePaths[self::FILE_OUT].'"', '"'.$tmpFile.'"');
            if ($result['return_code'] != 0)
                $this->result->error(
                    TestResult::ERROR_OUT_DIFF,
                    ['diff' => implode(PHP_EOL, $result['output'])],
                    file_get_contents($errorTmpFile)
                );
        }

        unlink($errorTmpFile);
        unlink($tmpFile);
    }

    /**
     * Get expected return code from file
     *
     * @return string return code
     */
    private function getReturnCode() {
        return file_get_contents($this->filePaths[self::FILE_RC]);
    }

    /**
     * @return string unique temporary file name
     */
    private function getTmpFileName() {
        return tempnam(TesterApp::getInstance()->getConfig('temp-dir'), 'test');
    }

    /**
     * Find other test files
     */
    private function findReferenceFiles() {
        foreach ($this->getReferenceFileTypes() as $fileType) {
            $filePath = \OSUtils::changeFileExtension($this->getSrcPath(), $this->getFileTypeExtension($fileType));
            if (file_exists($filePath))
                $this->filePaths[$fileType] = $filePath;
        }
    }

    /**
     * Generate missing test files (.rc, .in,...)
     */
    private function generateMissingFiles() {
        foreach ($this->filePaths as $fileType => &$filePath) {
            if ($filePath === null) {
                $filePath = \OSUtils::changeFileExtension($this->getSrcPath(), $this->getFileTypeExtension($fileType));
                $this->generateFile($filePath, $this->getFileTypeDefaultContent($fileType));
                $this->filePaths[$fileType] = $filePath;
            }
        }
    }

    /**
     * Generate file with content
     *
     * @param $filePath string file to create
     * @param $content string file content to write
     * @throws \OpenStreamException
     */
    private function generateFile($filePath, $content) {
        $stream = fopen($filePath, 'w');
        if ($stream === false)
            throw new \OpenStreamException('Error creating file: '.$filePath, \ExitCodes::ERROR_OPENING_FILE_IN);

        fwrite($stream, $content);
        fclose($stream);
    }

    /**
     * Get file types except .src
     *
     * @return array range of file types
     */
    private function getReferenceFileTypes() {
        return range(self::FILE_IN, self::FILE_RC);
    }

    /**
     * Get file type extension (src, rc,...)
     *
     * @param $fileType int file type defined by constants (FILE_SRC, FILE_RC, ...)
     * @return string extension without leading dot
     */
    private function getFileTypeExtension($fileType) {
        return self::FILE_INFO[$fileType]['extension'];
    }

    /**
     * Get default content for given file type
     *
     * @param $fileType int file type defined by constants (FILE_SRC, FILE_RC, ...)
     * @return string default content
     */
    private function getFileTypeDefaultContent($fileType) {
        return self::FILE_INFO[$fileType]['default'];
    }

    /**
     * Get source file path
     *
     * @return string source file path
     */
    private function getSrcPath() {
        return $this->filePaths[self::FILE_SRC];
    }
}