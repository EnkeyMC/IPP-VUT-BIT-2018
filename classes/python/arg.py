from classes.python.frame import Frame
from classes.python.exceptions import InternalError, OperandTypeError
from copy import copy


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


class ArgType:
    @staticmethod
    def arg_int(context, arg: Arg):
        if arg.get_data_type(context) != 'int':
            ArgType._raise_type_error(context, 'int', arg.get_data_type(context))

    @staticmethod
    def arg_bool(context, arg: Arg):
        if arg.get_data_type(context) != 'bool':
            ArgType._raise_type_error(context, 'bool', arg.get_data_type(context))

    @staticmethod
    def arg_string(context, arg: Arg):
        if arg.get_data_type(context) != 'string':
            ArgType._raise_type_error(context, 'string', arg.get_data_type(context))

    @staticmethod
    def arg_type(context, arg: Arg):
        if arg.type != 'type':
            ArgType._raise_type_error(context, 'type', arg.type)

    @staticmethod
    def arg_label(context, arg: Arg):
        if arg.type != 'label':
            ArgType._raise_type_error(context, 'int', arg.type)

    @staticmethod
    def arg_dest(context, arg: Arg):
        if arg.type != 'var':
            ArgType._raise_type_error(context, 'var', arg.type)

    @staticmethod
    def arg_any(context, arg: Arg):
        if arg.get_data_type(context) not in ['int', 'bool', 'string']:
            ArgType._raise_type_error(context, '[int, bool, string]', arg.get_data_type(context))

    @staticmethod
    def arg_dest_or_any(context, arg: Arg):
        if arg.type != 'var' and arg.type not in ['int', 'bool', 'string']:
            ArgType._raise_type_error(context, '[int, bool, string]', arg.get_data_type(context))

    @staticmethod
    def _raise_type_error(context, expected: str, actual: str):
        raise OperandTypeError(
            "Chyba instrukce {} ({}): Očekáván typ {}, skutečný: {}".format(
                context.get_inst_number(), context.get_current_inst().opcode, expected, actual
            )
        )
