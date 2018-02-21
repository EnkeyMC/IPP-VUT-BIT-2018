<?php

class SourceCodeException extends Exception {}

class LexicalErrorException extends SourceCodeException  {}

class SyntaxErrorException extends SourceCodeException  {}

class InvalidContextException extends Exception {}

class InvalidAddressTypeException extends Exception {}

class RegexErrorException extends Exception {}

class OpenStreamException extends Exception {}
