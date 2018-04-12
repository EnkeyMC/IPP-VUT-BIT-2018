from classes.python.exceptions import LexicalError, SrcSyntaxError
import re

__author__ = "Martin Omacht"
__copyright__ = "Copyright 2018"
__credits__ = ["Martin Omacht"]


_TYPE_REGEX = {
    'int': re.compile(r"^[+\-]?(0|[1-9]\d*)$"),
    'bool': re.compile(r"^(true|false)$"),
    'string': re.compile(r"^([^\s#\\]*(\\\d\d\d)?)*$", re.UNICODE),
    'label': re.compile(r"^[a-zA-Z\-_&$*%][a-zA-Z\-_&$*%0-9]*$"),
    'type': re.compile(r"^(int|bool|string)$"),
    'var': re.compile(r"^[LTG]F@[a-zA-Z\-_&$*%][a-zA-Z\-_&$*%0-9]*$")
}


def check_validity(val_type: str, value: str):
    """
    Check argument validity
    :param val_type: value type
    :param value: value
    """
    if val_type not in _TYPE_REGEX:
        raise SrcSyntaxError('Typ "{}" není validní'.format(val_type))
    if value is None:
        value = ''
    if not _TYPE_REGEX[val_type].match(value):
        raise LexicalError('Typ {} obsahuje nesprávnou hodnotu "{}"'.format(val_type, value))

