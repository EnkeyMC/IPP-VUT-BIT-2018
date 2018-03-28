from classes.python.ipp_parser import IPPParser
from classes.python.program import Program
from classes.python.exceptions import ApplicationError
import argparse
from sys import stderr
import subprocess


def exec_parser(src_file: str):
    stdin = open(src_file)
    result = subprocess.run(['php', 'parse.php'], check=True, stdout=subprocess.PIPE, stdin=stdin)
    return result.stdout


argparser = argparse.ArgumentParser()
argparser.add_argument('--source')
argparser.add_argument('--parse', action='store_const', const=True, default=False)
args = argparser.parse_args()

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
