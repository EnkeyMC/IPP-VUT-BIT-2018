from enum import Enum
from xml.etree.ElementTree import Element
from classes.python.exceptions import XMLFormatError


class ArgType(Enum):
    ARG_VAR = 0
    ARG_SYMB = 1
    ARG_LABEL = 2
    ARG_TYPE = 3


class IPPcode18:
    INSTRUCTION_LIST = {
        'MOVE': [ArgType.ARG_VAR, ArgType.ARG_SYMB],
        'CREATEFRAME': [],
        'PUSHFRAME': [],
        'POPFRAME': [],
        'DEFVAR': [ArgType.ARG_VAR],
        'CALL': [ArgType.ARG_LABEL],
        'RETURN': [],
        'PUSHS': [ArgType.ARG_SYMB],
        'POPS': [ArgType.ARG_VAR],
        'ADD': [ArgType.ARG_VAR, ArgType.ARG_SYMB, ArgType.ARG_SYMB],
        'SUB': [ArgType.ARG_VAR, ArgType.ARG_SYMB, ArgType.ARG_SYMB],
        'MUL': [ArgType.ARG_VAR, ArgType.ARG_SYMB, ArgType.ARG_SYMB],
        'IDIV': [ArgType.ARG_VAR, ArgType.ARG_SYMB, ArgType.ARG_SYMB],
        'LT': [ArgType.ARG_VAR, ArgType.ARG_SYMB, ArgType.ARG_SYMB],
        'GT': [ArgType.ARG_VAR, ArgType.ARG_SYMB, ArgType.ARG_SYMB],
        'EQ': [ArgType.ARG_VAR, ArgType.ARG_SYMB, ArgType.ARG_SYMB],
        'AND': [ArgType.ARG_VAR, ArgType.ARG_SYMB, ArgType.ARG_SYMB],
        'OR': [ArgType.ARG_VAR, ArgType.ARG_SYMB, ArgType.ARG_SYMB],
        'NOT': [ArgType.ARG_VAR, ArgType.ARG_SYMB],
        'INT2CHAR': [ArgType.ARG_VAR, ArgType.ARG_SYMB],
        'STRI2INT': [ArgType.ARG_VAR, ArgType.ARG_SYMB, ArgType.ARG_SYMB],
        'READ': [ArgType.ARG_VAR, ArgType.ARG_TYPE],
        'WRITE': [ArgType.ARG_SYMB],
        'CONCAT': [ArgType.ARG_VAR, ArgType.ARG_SYMB, ArgType.ARG_SYMB],
        'STRLEN': [ArgType.ARG_VAR, ArgType.ARG_SYMB],
        'GETCHAR': [ArgType.ARG_VAR, ArgType.ARG_SYMB, ArgType.ARG_SYMB],
        'SETCHAR': [ArgType.ARG_VAR, ArgType.ARG_SYMB, ArgType.ARG_SYMB],
        'TYPE': [ArgType.ARG_VAR, ArgType.ARG_SYMB],
        'LABEL': [ArgType.ARG_LABEL],
        'JUMP': [ArgType.ARG_LABEL],
        'JUMPIFEQ': [ArgType.ARG_LABEL, ArgType.ARG_SYMB, ArgType.ARG_SYMB],
        'JUMPIFNEQ': [ArgType.ARG_LABEL, ArgType.ARG_SYMB, ArgType.ARG_SYMB],
        'DPRINT': [ArgType.ARG_SYMB],
        'BREAK': []
    }

    @staticmethod
    def is_valid_arg(opcode: str, nth_arg: int, arg: Element) -> bool:
        assert opcode in IPPcode18.INSTRUCTION_LIST
        if not (1 <= nth_arg <= len(IPPcode18.INSTRUCTION_LIST[opcode])):
            raise XMLFormatError('Neplatný argument arg{} u operace {}'.format(nth_arg, opcode))
        arg_type = IPPcode18.INSTRUCTION_LIST[opcode][nth_arg-1]
        return True  # TODO

    @staticmethod
    def get_opcode_arg_num(opcode: str) -> int:
        assert opcode in IPPcode18.INSTRUCTION_LIST
        return len(IPPcode18.INSTRUCTION_LIST[opcode])
