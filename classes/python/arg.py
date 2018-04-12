from classes.python.frame import Frame
from classes.python.exceptions import InternalError, OperandTypeError, MissingValue, UndefinedVar
from copy import copy
import re

__author__ = "Martin Omacht"
__copyright__ = "Copyright 2018"
__credits__ = ["Martin Omacht"]


class Arg:
    """
    Class representing instruction argument
    """
    _esc_seq_re = None  # Compiled regex for escape sequences

    def __init__(self, arg_type: str, value: str):
        """
        Initialize argument
        :param arg_type: argument type
        :param value: value in string
        """
        if Arg._esc_seq_re is None:  # Compile regex the first time
            Arg._esc_seq_re = re.compile(r"(?<=\\)\d{3}")

        # Convert value based on type
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
        elif arg_type == 'string':
            self.value = self._convert_escapes(value)
        else:
            self.value = value

    def _convert_escapes(self, string: str):
        """
        Convert escape sequences in string to characters
        :param string: string to convert
        :return: converted string
        """
        matches = self._esc_seq_re.findall(string)
        converted = dict()
        for match in matches:
            converted[match] = chr(int(match))

        for seq, char in converted.items():
            string = string.replace("\\" + seq, char)
        return string

    def get_data_type(self, context):
        """
        Get type of data in this argument
        :param context: Program context
        :return: type string or empty string if type is None
        """
        if self.is_var():
            t = context.get_var(self.frame, self.value).type
            if t is None:
                return ''
            return t
        else:
            return self.type

    def set_value(self, context, value):
        """
        Set value of variable given by this argument.
        This argument has to be type variable.

        :param context: Program context
        :param value: value to set (Arg or int, bool, str)
        """
        assert self.is_var()
        frame = context.get_frame(self.frame)
        if self.value not in frame:
            raise UndefinedVar("Pokus o zápis do nedefinované proměnné")
        
        if type(value) is Arg:
            if value.type == 'var':
                frame[self.value] = copy(context.get_var(value.frame, value.value))
            else:
                frame[self.value].value = value.value
                frame[self.value].type = value.type
        else:
            frame[self.value].value = value
            if type(value) is int:
                frame[self.value].type = 'int'
            elif type(value) is bool:
                frame[self.value].type = 'bool'
            elif type(value) is str:
                frame[self.value].type = 'string'
            else:
                raise InternalError("Neznámý typ {}".format(type(value)))

    def get_value(self, context):
        """
        Get argument value, if argument is of type var, return value of the variable
        :param context: Program context
        :return: argument value
        """
        if self.is_var():
            return context.get_var(self.frame, self.value).value
        else:
            return self.value

    def is_var(self):
        """
        Is argument of type var
        :return: True if type is var, False otherwise
        """
        return self.type == 'var'

    def to_str(self, context):
        """
        Get argument value as string
        :param context: Program context
        :return: string representation of value
        """
        val = self.get_value(context)
        if type(val) is bool:
            if val:
                return 'true'
            else:
                return 'false'
        else:
            return str(val)


class ArgType:
    """
    Static class with argument checking static methods
    """
    @staticmethod
    def arg_int(context, arg: Arg):
        """
        Check if given argument is of type int, raises exception otherwise
        :param context: Program context
        :param arg: instruction argument
        """
        ArgType._check_if_initialized(context, arg)
        if arg.get_data_type(context) != 'int':
            ArgType._raise_type_error(context, 'int', arg.get_data_type(context))

    @staticmethod
    def arg_bool(context, arg: Arg):
        """
        Check if given argument is of type bool, raises exception otherwise
        :param context: Program context
        :param arg: instruction argument
        """
        ArgType._check_if_initialized(context, arg)
        if arg.get_data_type(context) != 'bool':
            ArgType._raise_type_error(context, 'bool', arg.get_data_type(context))

    @staticmethod
    def arg_string(context, arg: Arg):
        """
        Check if given argument is of type string, raises exception otherwise
        :param context: Program context
        :param arg: instruction argument
        """
        ArgType._check_if_initialized(context, arg)
        if arg.get_data_type(context) != 'string':
            ArgType._raise_type_error(context, 'string', arg.get_data_type(context))

    @staticmethod
    def arg_type(context, arg: Arg):
        """
        Check if given argument is of type type, raises exception otherwise
        :param context: Program context
        :param arg: instruction argument
        """
        if arg.type != 'type':
            ArgType._raise_type_error(context, 'type', arg.type)

    @staticmethod
    def arg_label(context, arg: Arg):
        """
        Check if given argument is of type label, raises exception otherwise
        :param context: Program context
        :param arg: instruction argument
        """
        if arg.type != 'label':
            ArgType._raise_type_error(context, 'int', arg.type)

    @staticmethod
    def arg_dest(context, arg: Arg):
        """
        Check if given argument is valid destination (variable), raises exception otherwise
        :param context: Program context
        :param arg: instruction argument
        """
        if arg.type != 'var':
            ArgType._raise_type_error(context, 'var', arg.type)

    @staticmethod
    def arg_any(context, arg: Arg):
        """
        Check if given argument is of type int, bool or string, raises exception otherwise
        :param context: Program context
        :param arg: instruction argument
        """
        ArgType._check_if_initialized(context, arg)
        if arg.get_data_type(context) not in ['int', 'bool', 'string']:
            ArgType._raise_type_error(context, '[int, bool, string]', arg.get_data_type(context))

    @staticmethod
    def arg_dest_or_any(context, arg: Arg):
        """
        Check if given argument is of type int, bool, string or var, raises exception otherwise
        :param context: Program context
        :param arg: instruction argument
        """
        if arg.type != 'var' and arg.type not in ['int', 'bool', 'string']:
            ArgType._raise_type_error(context, '[int, bool, string]', arg.get_data_type(context))

    @staticmethod
    def _raise_type_error(context, expected: str, actual: str):
        """
        Raise OperandTypeError exception
        :param context: Program context
        :param expected: Expected type
        :param actual: Actual type
        """
        raise OperandTypeError(
            "Chyba instrukce {} ({}): Očekáván typ {}, skutečný: {}".format(
                context.get_inst_number(), context.get_current_inst().opcode, expected, actual
            )
        )

    @staticmethod
    def _check_if_initialized(context, arg: Arg):
        """
        Check if given argument is initialized, raises MissingValue otherwise
        :param context: Program context
        :param arg: instruction argument
        """
        if arg.get_data_type(context) == '':
            raise MissingValue("Pokus o čtení neinicializované proměnné")
