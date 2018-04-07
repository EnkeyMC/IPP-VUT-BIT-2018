from classes.python.ipp_parser import IPPParser
from classes.python.program import Program
from classes.python.exceptions import ApplicationError
from classes.python.exit_codes import ARGUMENT_ERROR
import argparse
from sys import stderr
import subprocess


def exec_parser(src_file: str):
    stdin = open(src_file)
    result = subprocess.run(['php', 'parse.php'], check=True, stdout=subprocess.PIPE, stdin=stdin)
    return result.stdout


argparser = argparse.ArgumentParser(description='Interprets XML representaion of IPPcode18.')
argparser.add_argument('--source', required=True, help='Source file to interpret')
argparser.add_argument('--parse', action='store_const', const=True, default=False,
                       help='Interpret asks for file name to parse and then interpret (for dev purposes)')
args = None
try:
    args = argparser.parse_args()
except SystemExit:
    exit(ARGUMENT_ERROR)

parser = IPPParser()
xml_dom = None
if args.parse:
    file = input('File to parse: ')
    xml = exec_parser(file)
    try:
        xml_dom = parser.parse_from_string(xml)
    except ApplicationError as err:
        print(err.get_message(), file=stderr)
        exit(err.get_exit_code())
else:
    try:
        xml_dom = parser.parse_from_file(args.source)
    except ApplicationError as err:
        print(err.get_message(), file=stderr)
        exit(err.get_exit_code())

program = Program(xml_dom)
try:
    program.analyze()
except ApplicationError as err:
    print(err.get_message(), file=stderr)
    exit(err.get_exit_code())

try:
    program.interpret()
except ApplicationError as err:
    print("Chyba instukce {} ({}): "
          .format(program.get_inst_number(), program.get_current_inst().opcode) + err.get_message(), file=stderr)
    exit(err.get_exit_code())
