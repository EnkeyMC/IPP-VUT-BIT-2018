from xml.etree.ElementTree import Element
from classes.python.frame import Frame
from classes.python.exceptions import InternalError
from copy import copy
import operator
import sys


class Arg:

    def __init__(self, arg_type: str, value: str):
        self.type = arg_type
        if arg_type == 'var':
            parts = value.split('@')
            self.frame = Frame.str_to_frame(parts[0])
            self.value = parts[1]
        elif arg_type == 'int':
            self.value = int(value)
        elif arg_type == 'bool':
            if value == 'true':
                self.value = True
            else:
                self.value = False
        else:
            self.value = value

    def get_data_type(self, context):
        if self.is_var():
            t = context.get_var(self.frame, self.value).type
            if t is None:
                return ''
            return t
        else:
            return self.type

    def set_value(self, context, value):
        assert self.is_var()
        if type(value) is Arg:
            if value.type == 'var':
                context.get_frame(self.frame)[self.value] = copy(context.get_var(value.frame, value.value))
            else:
                context.get_frame(self.frame)[self.value].value = value.value
                context.get_frame(self.frame)[self.value].type = value.type
        else:
            context.get_frame(self.frame)[self.value].value = value
            if type(value) is int:
                context.get_frame(self.frame)[self.value].type = 'int'
            elif type(value) is bool:
                context.get_frame(self.frame)[self.value].type = 'bool'
            elif type(value) is str:
                context.get_frame(self.frame)[self.value].type = 'string'
            else:
                raise InternalError("Neznámý typ {}".format(type(value)))

    def get_value(self, context):
        if self.is_var():
            return context.get_var(self.frame, self.value).value
        else:
            return self.value

    def is_var(self):
        return self.type == 'var'

    def to_str(self, context):
        val = self.get_value(context)
        if type(val) is bool:
            if val:
                return 'true'
            else:
                return 'false'
        else:
            return str(val)


class Instruction:

    def __init__(self, opcode: str, args: list):
        self.opcode = opcode
        self.args = args
        self.func = INST_MAPPING[opcode]

    def run(self, context):
        self.func(context, *self.args)

    @staticmethod
    def from_xml_dom(inst_dom: Element):
        opcode = inst_dom.attrib['opcode']
        args = list()

        for arg in inst_dom:
            args.append(Arg(arg.attrib['type'], arg.text))

        return Instruction(opcode, args)


def _move(context, dest: Arg, src: Arg):
    dest.set_value(context, src)


def _binary_operation(context, dest: Arg, op1: Arg, op2: Arg, op):
    dest.set_value(context, op(op1.get_value(context), op2.get_value(context)))


def _createframe(context):
    context.create_tmp_frame()


def _pushframe(context):
    context.push_tmp_frame()


def _popframe(context):
    context.pop_to_tmp_frame()


def _defvar(context, var: Arg):
    context.create_var(var.frame, var.value)


def _call(context, label: Arg):
    context.call(label.value)


def _return(context):
    context.ret()


def _pushs(context, symb: Arg):
    context.data_push(symb.get_value(context))


def _pops(context, var: Arg):
    var.set_value(context, context.data_pop())


def _add(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.add)


def _sub(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.sub)


def _mul(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.mul)


def _idiv(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.floordiv)


def _lt(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.lt)


def _gt(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.gt)


def _eq(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.eq)


def _and(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.and_)


def _or(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.or_)


def _not(context, dest: Arg, op1: Arg):
    dest.set_value(context, not op1.get_value(context))


def _int2char(context, dest: Arg, idx: Arg):
    dest.set_value(context, chr(idx.get_value(context)))


def _stri2int(context, dest: Arg, src_str: Arg, idx: Arg):
    dest.set_value(context, ord(src_str.get_value(context)[idx.get_value(context)]))


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


def _write(context, symb: Arg):
    print(symb.to_str(context))


def _concat(context, dest: Arg, op1: Arg, op2: Arg):
    _binary_operation(context, dest, op1, op2, operator.add)


def _strlen(context, dest: Arg, string: Arg):
    dest.set_value(context, len(string.get_value(context)))


def _getchar(context, dest: Arg, string: Arg, idx: Arg):
    dest.set_value(context, string.get_value(context)[idx.get_value(context)])


def _setchar(context, dest: Arg, idx: Arg, src: Arg):
    val = dest.get_value(context)
    index = idx.get_value(context)
    val = val[:index] + src.get_value(context)[0] + val[index+1:]
    dest.set_value(context, val)


def _type(context, dest: Arg, src: Arg):
    dest.set_value(context, src.get_data_type(context))


def _label(context, label: Arg):
    pass


def _jump(context, label: Arg):
    context.jump_to_label(label.get_value(context))


def _jumpifeq(context, label: Arg, op1: Arg, op2: Arg):
    if op1.get_value(context) == op2.get_value(context):
        context.jump_to_label(label.get_value(context))


def _jumpifneq(context, label: Arg, op1: Arg, op2: Arg):
    if op1.get_value(context) != op2.get_value(context):
        context.jump_to_label(label.get_value(context))


def _dprint(context, symb: Arg):
    print(symb.to_str(context), file=sys.stderr)


def _break(context):
    context.print_debug()


INST_MAPPING = {
    'MOVE': _move,
    'CREATEFRAME': _createframe,
    'PUSHFRAME': _pushframe,
    'POPFRAME': _popframe,
    'DEFVAR': _defvar,
    'CALL': _call,
    'RETURN': _return,
    'PUSHS': _pushs,
    'POPS': _pops,
    'ADD': _add,
    'SUB': _sub,
    'MUL': _mul,
    'IDIV': _idiv,
    'LT': _lt,
    'GT': _gt,
    'EQ': _eq,
    'AND': _and,
    'OR': _or,
    'NOT': _not,
    'INT2CHAR': _int2char,
    'STRI2INT': _stri2int,
    'READ': _read,
    'WRITE': _write,
    'CONCAT': _concat,
    'STRLEN': _strlen,
    'GETCHAR': _getchar,
    'SETCHAR': _setchar,
    'TYPE': _type,
    'LABEL': _label,
    'JUMP': _jump,
    'JUMPIFEQ': _jumpifeq,
    'JUMPIFNEQ': _jumpifneq,
    'DPRINT': _dprint,
    'BREAK': _break
}
