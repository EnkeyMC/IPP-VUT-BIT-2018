from unittest.case import TestCase

from classes.python.ipp_parser import IPPParser


class TestIPPParser(TestCase):
    def setUp(self):
        self.parser = IPPParser()

    def test_empty(self):
        self.assertTrue(self.parser.parse_from_string(
            """<?xml version="1.0" encoding="UTF-8"?><program language="IPPcode18"></program>"""))
