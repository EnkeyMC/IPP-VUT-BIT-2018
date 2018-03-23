from classes.python.frame import Frame
from classes.python.exceptions import InternalError
from copy import copy
from enum import Enum


class ArgType(Enum):
    ARG_INT = 0
    ARG_BOOL = 1
    ARG_STRING = 2
    ARG_TYPE = 3
    ARG_LABEL = 4
    ARG_VAR = 5
    ARG_SYMB = 6


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
