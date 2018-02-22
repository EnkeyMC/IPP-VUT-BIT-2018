<?php

namespace TestSuite;


class TestCase
{
    const FILE_SRC = 0;
    const FILE_IN = 1;
    const FILE_OUT = 2;
    const FILE_RC = 3;

    const FILE_INFO = [
        self::FILE_SRC => ['extension' => 'src', 'default' => ''],
        self::FILE_IN => ['extension' => 'in', 'default' => ''],
        self::FILE_OUT => ['extension' => 'out', 'default' => ''],
        self::FILE_RC => ['extension' => 'rc', 'default' => '0']
    ];

    private $filePaths = [
        self::FILE_SRC => null,
        self::FILE_IN => null,
        self::FILE_OUT => null,
        self::FILE_RC => null
    ];

    public function __construct($srcFilePath)
    {
        $this->filePaths[self::FILE_SRC] = $srcFilePath;
        $this->findReferenceFiles();
        $this->generateMissingFiles();
    }

    public function run() {
        $app = TesterApp::getInstance();
        $tmpFlie = tempnam($app->getConfig('temp-dir'), 'xml');
        $result = \OSUtils::runCommand(
            $app->getConfig('php-int'),
            [$app->getConfig('parse-script')],
            $this->filePaths[self::FILE_SRC],
            $tmpFlie
        );


    }

    private function findReferenceFiles() {
        foreach ($this->getReferenceFileTypes() as $fileType) {
            $filePath = \OSUtils::changeFileExtension($this->getSrcPath(), $this->getFileTypeExtension($fileType));
            if (file_exists($filePath))
                $this->filePaths[$fileType] = $filePath;
        }
    }

    private function generateMissingFiles() {
        foreach ($this->filePaths as $fileType => &$filePath) {
            if ($filePath === null) {
                $filePath = \OSUtils::changeFileExtension($this->getSrcPath(), $this->getFileTypeExtension($fileType));
                $this->generateFile($filePath, $this->getFileTypeDefaultContent($fileType));
                $this->filePaths[$fileType] = $filePath;
            }
        }
    }

    private function generateFile($filePath, $content) {
        $stream = fopen($filePath, 'w');
        if ($stream === false)
            throw new \OpenStreamException('Error creating file: '.$filePath, \ExitCodes::ERROR_OPENING_FILE_IN);

        fwrite($stream, $content);
        fclose($stream);
    }

    private function getReferenceFileTypes() {
        return range(self::FILE_IN, self::FILE_RC);
    }

    private function getFileTypeExtension($fileType) {
        return self::FILE_INFO[$fileType]['extension'];
    }

    private function getFileTypeDefaultContent($fileType) {
        return self::FILE_INFO[$fileType]['default'];
    }

    private function getSrcPath() {
        return $this->filePaths[self::FILE_SRC];
    }
}