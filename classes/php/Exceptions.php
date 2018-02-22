<?php

class SourceCodeException extends Exception {}

class LexicalErrorException extends SourceCodeException  {}

class SyntaxErrorException extends SourceCodeException  {}

class InvalidContextException extends Exception {}

class InvalidAddressTypeException extends Exception {}

class RegexErrorException extends Exception {}

class OpenStreamException extends Exception {}

// TestSuite exception
class TestSuiteException extends Exception {}

class WrongReturnCodeException extends TestSuiteException {}

class ParseWrongReturnCodeException extends WrongReturnCodeException {}
