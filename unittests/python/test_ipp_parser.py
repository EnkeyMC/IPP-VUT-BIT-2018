from unittest.case import TestCase

from classes.python.ipp_parser import IPPParser
from classes.python.exceptions import XMLFormatError


class TestIPPParser(TestCase):
    def setUp(self):
        self.parser = IPPParser()

    def test_empty(self):
        self.skipTest("for now")  # TODO don't skip later
        self.assertTrue(self.parser.parse_from_string(
            """<?xml version="1.0" encoding="UTF-8"?><program language="IPPcode18"></program>"""))

    def test_no_root_tag(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>"""
        )

    def test_no_lang_attrib(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?><program></program>"""
        )

    def test_name_attrib(self):
        try:
            self.parser.parse_from_string(
                """<?xml version="1.0" encoding="UTF-8" ?><program language="IPPcode18" name="test"></program>"""
            )
        except Exception as e:
            self.fail("Exception {} was thrown!".format(repr(e)))

    def test_description_attrib(self):
        try:
            self.parser.parse_from_string(
                """<?xml version="1.0" encoding="UTF-8" ?><program language="IPPcode18" description="test"></program>"""
            )
        except Exception as e:
            self.fail("Exception {} was thrown!".format(repr(e)))

    def test_invalid_program_attrib(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?><program language="IPPcode18" invalid="test"></program>"""
        )
