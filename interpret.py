from classes.python.ipp_parser import IPPParser
from classes.python.program import Program
from classes.python.exceptions import ApplicationError
import argparse
from sys import stderr


argparser = argparse.ArgumentParser()
argparser.add_argument('--source', metavar='source', nargs='?')
args = argparser.parse_args()
parser = IPPParser()
try:
    xml_dom = parser.parse_from_file(args.source)
    program = Program(xml_dom)
    program.analyze()
    program.interpret()
except ApplicationError as err:
    print(err.get_message(), file=stderr)
    exit(err.get_exit_code())
