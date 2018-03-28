from classes.python.exceptions import XMLFormatError, DivisionByZeroError, OperandTypeError, StringOperationError
from classes.python.arg import Arg, ArgType
from xml.etree.ElementTree import Element
import operator
import sys


class Instruction:
    INSTRUCTION_ARGS = {
        'MOVE': [ArgType.arg_dest, ArgType.arg_any],
        'CREATEFRAME': [],
        'PUSHFRAME': [],
        'POPFRAME': [],
        'DEFVAR': [ArgType.arg_dest],
        'CALL': [ArgType.arg_label],
        'RETURN': [],
        'PUSHS': [ArgType.arg_any],
        'POPS': [ArgType.arg_dest],
        'ADD': [ArgType.arg_dest, ArgType.arg_int, ArgType.arg_int],
        'SUB': [ArgType.arg_dest, ArgType.arg_int, ArgType.arg_int],
        'MUL': [ArgType.arg_dest, ArgType.arg_int, ArgType.arg_int],
        'IDIV': [ArgType.arg_dest, ArgType.arg_int, ArgType.arg_int],
        'LT': [ArgType.arg_dest, ArgType.arg_any, ArgType.arg_any],
        'GT': [ArgType.arg_dest, ArgType.arg_any, ArgType.arg_any],
        'EQ': [ArgType.arg_dest, ArgType.arg_any, ArgType.arg_any],
        'AND': [ArgType.arg_dest, ArgType.arg_bool, ArgType.arg_bool],
        'OR': [ArgType.arg_dest, ArgType.arg_bool, ArgType.arg_bool],
        'NOT': [ArgType.arg_dest, ArgType.arg_bool],
        'INT2CHAR': [ArgType.arg_dest, ArgType.arg_int],
        'STRI2INT': [ArgType.arg_dest, ArgType.arg_string, ArgType.arg_int],
        'READ': [ArgType.arg_dest, ArgType.arg_type],
        'WRITE': [ArgType.arg_any],
        'CONCAT': [ArgType.arg_dest, ArgType.arg_string, ArgType.arg_string],
        'STRLEN': [ArgType.arg_dest, ArgType.arg_string],
        'GETCHAR': [ArgType.arg_dest, ArgType.arg_string, ArgType.arg_int],
        'SETCHAR': [ArgType.arg_string, ArgType.arg_int, ArgType.arg_string],
        'TYPE': [ArgType.arg_dest, ArgType.arg_dest_or_any],
        'LABEL': [ArgType.arg_label],
        'JUMP': [ArgType.arg_label],
        'JUMPIFEQ': [ArgType.arg_label, ArgType.arg_any, ArgType.arg_any],
        'JUMPIFNEQ': [ArgType.arg_label, ArgType.arg_any, ArgType.arg_any],
        'DPRINT': [ArgType.arg_dest_or_any],
        'BREAK': []
    }

    _inst_mapping = dict()

    def __init__(self, opcode: str, args: list):
        self.opcode = opcode
        self.args = args

    def run(self, context):
        self._check_arguments(context)
        Instruction._inst_mapping[self.opcode](context, *self.args)

    def _check_arguments(self, context):
        for i in range(0, len(self.args)):
            Instruction.INSTRUCTION_ARGS[self.opcode][i](context, self.args[i])

    @staticmethod
    def run_func(func: callable):
        name = func.__name__
        name = name[1:]
        Instruction._inst_mapping[name.upper()] = func
        return func

    @staticmethod
    def from_xml_dom(inst_dom: Element):
        opcode = inst_dom.attrib['opcode']
        args = list()

        for arg in inst_dom:
            if arg.text is None:
                arg.text = ''
            args.append(Arg(arg.attrib['type'], arg.text))

        return Instruction(opcode, args)

    @staticmethod
    def is_valid_arg(opcode: str, nth_arg: int, arg: Element) -> bool:
        assert opcode in Instruction.INSTRUCTION_ARGS
        if not (1 <= nth_arg <= len(Instruction.INSTRUCTION_ARGS[opcode])):
            raise XMLFormatError('Neplatný argument arg{} u operace {}'.format(nth_arg, opcode))
        arg_type = Instruction.INSTRUCTION_ARGS[opcode][nth_arg - 1]
        return True  # TODO

    @staticmethod
    def get_opcode_arg_num(opcode: str) -> int:
        assert opcode in Instruction.INSTRUCTION_ARGS
        return len(Instruction.INSTRUCTION_ARGS[opcode])


def _binary_operation(context, dest: Arg, op1: Arg, op2: Arg, op):
    dest.set_value(context, op(op1.get_value(context), op2.get_value(context)))


def _check_if_same_type(context, arg1: Arg, arg2: Arg):
    if arg1.get_data_type(context) != arg2.get_data_type(context):
        raise OperandTypeError("Argumenty instrukce {} ({}) musí být stejného typu"
                               .format(context.get_current_inst().opcode, context.get_inst_number()))


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
    if op2.get_value(context) == 0:
        raise DivisionByZeroError("Pokus o dělení nulou")
    _binary_operation(context, dest, op1, op2, operator.floordiv)


@Instruction.run_func
def _lt(context, dest: Arg, op1: Arg, op2: Arg):
    _check_if_same_type(context, op1, op2)
    _binary_operation(context, dest, op1, op2, operator.lt)


@Instruction.run_func
def _gt(context, dest: Arg, op1: Arg, op2: Arg):
    _check_if_same_type(context, op1, op2)
    _binary_operation(context, dest, op1, op2, operator.gt)


@Instruction.run_func
def _eq(context, dest: Arg, op1: Arg, op2: Arg):
    _check_if_same_type(context, op1, op2)
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
    try:
        dest.set_value(context, chr(idx.get_value(context)))
    except ValueError:
        raise StringOperationError("Neplatná ordinální hodnota Unicode: {}".format(idx.get_value(context)))


@Instruction.run_func
def _stri2int(context, dest: Arg, src_str: Arg, idx: Arg):
    try:
        dest.set_value(context, ord(src_str.get_value(context)[idx.get_value(context)]))
    except IndexError:
        raise StringOperationError("Neplatný index {}".format(idx.get_value(context)))


@Instruction.run_func
def _read(context, var: Arg, in_type: Arg):
    read = input()
    if in_type.value == 'int':
        try:
            var.set_value(context, int(read))
        except ValueError:
            var.set_value(context, 0)
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
    try:
        dest.set_value(context, string.get_value(context)[idx.get_value(context)])
    except IndexError:
        raise StringOperationError("Neplatný index {}".format(idx.get_value(context)))


@Instruction.run_func
def _setchar(context, dest: Arg, idx: Arg, src: Arg):
    val = dest.get_value(context)
    index = idx.get_value(context)
    src_val = src.get_value(context)
    if index >= len(val) or index < 0:
        raise StringOperationError("Neplatný index {}".format(index))
    if len(src_val) == 0:
        raise StringOperationError("Prázdný zdrojový řetězec")
    val = val[:index] + src_val[0] + val[index + 1:]
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
    _check_if_same_type(context, op1, op2)
    if op1.get_value(context) == op2.get_value(context):
        context.jump_to_label(label.get_value(context))


@Instruction.run_func
def _jumpifneq(context, label: Arg, op1: Arg, op2: Arg):
    _check_if_same_type(context, op1, op2)
    if op1.get_value(context) != op2.get_value(context):
        context.jump_to_label(label.get_value(context))


@Instruction.run_func
def _dprint(context, symb: Arg):
    print(symb.to_str(context), file=sys.stderr)


@Instruction.run_func
def _break(context):
    context.print_debug()
