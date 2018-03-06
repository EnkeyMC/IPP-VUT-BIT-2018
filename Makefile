ZIP_NAME=xomach00.zip
PHP_CLASSES=$(wildcard classes/php/*.php)
PY_CLASSES=$(wildcard classes/python/*.py)
TESTS=$(shell find tests/ -type f -name '*.in') $(shell find tests/ -type f -name '*.src') $(shell find tests/ -type f -name '*.out') $(shell find tests/ -type f -name '*.rc')
SCRIPTS=parse.php test.php interpret.py
ADDITIONAL=rozsireni autoload.php

pack:
	zip $(ZIP_NAME) $(PHP_CLASSES) $(PY_CLASSES) $(TESTS) $(SCRIPTS) $(ADDITIONAL)
