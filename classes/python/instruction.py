from xml.etree.ElementTree import Element
from classes.python.frame import Frame
from copy import copy
import operator


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
    if src.type == 'var':
        context.get_frame(dest.frame)[dest.value] = copy(context.get_frame(src.frame)[src.value])
    else:
        var = context.get_frame(dest.frame)[dest.value]
        var.value = src.value
        var.type = src.type
    context.next_inst()


def _binary_operation(context, dest: Arg, op1: Arg, op2: Arg, op):
    context.get_frame(dest.frame)[dest.value] = op(
        context.get_var(op1.frame, op1.value).value,
        context.get_var(op2.frame, op2.value).value
    )


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
    context.get_frame(dest.frame)[dest.value] = not context.get_var(op1.frame, op1.value).value


def _int2char(context, dest: Arg, idx: Arg):
    if idx.type == 'var':
        context.get_frame(dest.frame)[dest.value] = chr(context.get_var(idx.frame, idx.value).value)
    else:
        context.get_frame(dest.frame)[dest.value] = chr(idx.value)


def _stri2int(context, dest: Arg, src_str: Arg, idx: Arg):
    if idx.type == 'var':
        index = context.get_var(idx.frame, idx.value).value
    else:
        index = idx.value

    if src_str.type == 'var':
        string = context.get_var(idx.frame, idx.value).value
    else:
        string = src_str.value

    context.get_frame(dest.frame)[dest.value] = ord(string[index])


def _write(context, symb: Arg):
    if symb.type == 'bool':
        if symb.value:
            print('true')
        else:
            print('false')
    elif symb.type == 'var':
        val = context.get_var(symb.frame, symb.value)
        if val.type == 'bool':
            if val.value:
                print('true')
            else:
                print('false')
        else:
            print(val.value)
    else:
        print(symb.value)


INST_MAPPING = {
    'MOVE': _move,
    'CREATEFRAME': _createframe,
    'PUSHFRAME': _pushframe,
    'POPFRAME': _popframe,
    'DEFVAR': _defvar,
    'CALL': _call,
    'RETURN': _return,
    'ADD': _add,
    'SUB': _sub,
    'MUL': _mul,
    'LT': _lt,
    'GT': _gt,
    'EQ': _eq,
    'AND': _and,
    'OR': _or,
    'NOT': _not,
    'INT2CHAR': _int2char,
    'STRI2INT': _stri2int,
    'READ': None,  # TODO read
    'WRITE': _write,
}
