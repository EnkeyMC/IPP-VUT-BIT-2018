from classes.python.exceptions import XMLFormatError
from classes.python.arg import Arg, ArgType
from xml.etree.ElementTree import Element
import operator
import sys


class Instruction:
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

    inst_mapping = dict()

    def __init__(self, opcode: str, args: list):
        self.opcode = opcode
        self.args = args
        
    def run(self, context):
        Instruction.inst_mapping[self.opcode](context, *self.args)

    @staticmethod
    def run_func(func: callable):
        name = func.__name__
        name = name[1:]
        Instruction.inst_mapping[name.upper()] = func
        return func
    
    @staticmethod
    def from_xml_dom(inst_dom: Element):
        opcode = inst_dom.attrib['opcode']
        args = list()
    
        for arg in inst_dom:
            args.append(Arg(arg.attrib['type'], arg.text))
    
        return Instruction(opcode, args)

    @staticmethod
    def is_valid_arg(opcode: str, nth_arg: int, arg: Element) -> bool:
        assert opcode in Instruction.INSTRUCTION_LIST
        if not (1 <= nth_arg <= len(Instruction.INSTRUCTION_LIST[opcode])):
            raise XMLFormatError('Neplatný argument arg{} u operace {}'.format(nth_arg, opcode))
        arg_type = Instruction.INSTRUCTION_LIST[opcode][nth_arg - 1]
        return True  # TODO

    @staticmethod
    def get_opcode_arg_num(opcode: str) -> int:
        assert opcode in Instruction.INSTRUCTION_LIST
        return len(Instruction.INSTRUCTION_LIST[opcode])


def _binary_operation(context, dest: Arg, op1: Arg, op2: Arg, op):
    dest.set_value(context, op(op1.get_value(context), op2.get_value(context)))


@Instruction.run_func
def _move(context, dest: Arg, src: Arg):
    dest.set_value(context, src)


@Instruction.run_func
def _createframe(context):
    context.create_tmp_frame()


@Instruction.run_func
def _pushframe(context):
    context.push_tmp_frame()


@Instruction.run_func
def _popframe(context):
    context.pop_to_tmp_frame()


@Instruction.run_func
def _defvar(context, var: Arg):
    context.create_var(var.frame, var.value)


@Instruction.run_func
def _call(context, label: Arg):
    context.call(label.value)


@Instruction.run_func
def _return(context):
    context.ret()


@Instruction.run_func
def _pushs(context, symb: Arg):
    context.data_push(symb.get_value(context))


@Instruction.run_func
def _pops(context, var: Arg):
    var.set_value(context, context.data_pop())


@Instruction.run_func
def _add(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.add)


@Instruction.run_func
def _sub(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.sub)


@Instruction.run_func
def _mul(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.mul)


@Instruction.run_func
def _idiv(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.floordiv)


@Instruction.run_func
def _lt(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.lt)


@Instruction.run_func
def _gt(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.gt)


@Instruction.run_func
def _eq(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.eq)


@Instruction.run_func
def _and(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.and_)


@Instruction.run_func
def _or(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.or_)


@Instruction.run_func
def _not(context, dest: Arg, op1: Arg):
    dest.set_value(context, not op1.get_value(context))


@Instruction.run_func
def _int2char(context, dest: Arg, idx: Arg):
    dest.set_value(context, chr(idx.get_value(context)))


@Instruction.run_func
def _stri2int(context, dest: Arg, src_str: Arg, idx: Arg):
    dest.set_value(context, ord(src_str.get_value(context)[idx.get_value(context)]))


@Instruction.run_func
def _read(context, var: Arg, in_type: Arg):
    read = input()
    if in_type.value == 'int':
        var.set_value(context, int(read))
    elif in_type.value == 'string':
        var.set_value(context, read)
    else:
        if read.lower() == 'true':
            var.set_value(context, True)
        else:
            var.set_value(context, False)


@Instruction.run_func
def _write(context, symb: Arg):
    print(symb.to_str(context))


@Instruction.run_func
def _concat(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.add)


@Instruction.run_func
def _strlen(context, dest: Arg, string: Arg):
    dest.set_value(context, len(string.get_value(context)))


@Instruction.run_func
def _getchar(context, dest: Arg, string: Arg, idx: Arg):
    dest.set_value(context, string.get_value(context)[idx.get_value(context)])


@Instruction.run_func
def _setchar(context, dest: Arg, idx: Arg, src: Arg):
    val = dest.get_value(context)
    index = idx.get_value(context)
    val = val[:index] + src.get_value(context)[0] + val[index + 1:]
    dest.set_value(context, val)


@Instruction.run_func
def _type(context, dest: Arg, src: Arg):
    dest.set_value(context, src.get_data_type(context))


@Instruction.run_func
def _label(context, label: Arg):
    pass


@Instruction.run_func
def _jump(context, label: Arg):
    context.jump_to_label(label.get_value(context))


@Instruction.run_func
def _jumpifeq(context, label: Arg, op1: Arg, op2: Arg):
    if op1.get_value(context) == op2.get_value(context):
        context.jump_to_label(label.get_value(context))


@Instruction.run_func
def _jumpifneq(context, label: Arg, op1: Arg, op2: Arg):
    if op1.get_value(context) != op2.get_value(context):
        context.jump_to_label(label.get_value(context))


@Instruction.run_func
def _dprint(context, symb: Arg):
    print(symb.to_str(context), file=sys.stderr)


@Instruction.run_func
def _break(context):
    context.print_debug()
