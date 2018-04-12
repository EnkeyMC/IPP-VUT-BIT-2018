from classes.python.ipp_parser import IPPParser
from classes.python.program import Program
from classes.python.exceptions import ApplicationError
from classes.python.exit_codes import ARGUMENT_ERROR
import argparse
from sys import stderr, argv
import subprocess

__author__ = "Martin Omacht"
__copyright__ = "Copyright 2018"
__credits__ = ["Martin Omacht"]


def exec_parser(src_file: str):
    """
    Parse given file with parse.php
    :param src_file: file with source code
    :return: source code XML representation
    """
    stdin = open(src_file)
    result = subprocess.run(['php', 'parse.php'], check=True, stdout=subprocess.PIPE, stdin=stdin)
    return result.stdout


argparser = argparse.ArgumentParser(description='Interprets XML representaion of IPPcode18.', add_help=False)
argparser.add_argument('--source', required=True, help='Source file to interpret')
argparser.add_argument('--parse', action='store_const', const=True, default=False,
                       help='Interpret asks for file name to parse and then interpret (for dev purposes)')

if len(argv) == 2 and argv[1] in ('-h', '--help'):  # Argparse returns code 1 on help, have to do it manually
    argparser.print_help()
    exit(0)

# Parse arguments
args = None
try:
    args = argparser.parse_args()
except SystemExit:
    exit(ARGUMENT_ERROR)

# Parse XML
parser = IPPParser()
xml_dom = None
if args.parse:  # Mainly for debugging
    file = input('File to parse: ')
    xml = exec_parser(file)
    try:
        xml_dom = parser.parse_from_string(xml)
    except ApplicationError as err:
        print(err.get_message(), file=stderr)
        exit(err.get_exit_code())
else:  # Parse --source XML
    try:
        xml_dom = parser.parse_from_file(args.source)
    except ApplicationError as err:
        print(err.get_message(), file=stderr)
        exit(err.get_exit_code())

# Analyze program
program = Program(xml_dom)
try:
    program.analyze()
except ApplicationError as err:
    print(err.get_message(), file=stderr)
    exit(err.get_exit_code())

# Interpret
try:
    program.interpret()
except ApplicationError as err:
    print("Chyba instukce {} ({}): "
          .format(program.get_inst_number(), program.get_current_inst().opcode) + err.get_message(), file=stderr)
    exit(err.get_exit_code())
