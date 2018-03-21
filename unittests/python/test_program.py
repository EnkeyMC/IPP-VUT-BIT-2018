from unittest.case import TestCase

from classes.python.ipp_parser import IPPParser
from classes.python.program import Program


class TestProgram(TestCase):

    def setUp(self):
        self.parser = IPPParser()

    def test_aritmetic_simple(self):
        xml_dom = self.parser.parse_from_file('../files/output1.xml')
        program = Program(xml_dom)
        program.analyze()
