from xml.etree.ElementTree import Element

import sys

from classes.python.instruction import Instruction
from classes.python.exceptions import SemanticError, UndefinedVar, UndefinedFrame, MissingValue
from classes.python.frame import Frame

__author__ = "Martin Omacht"
__copyright__ = "Copyright 2018"
__credits__ = ["Martin Omacht"]


class Variable:
    """
    Class representing program variable
    """
    def __init__(self, var_type=None, value=None):
        """
        Initialize new variable, without arguments creates uninitialized variable
        :param var_type: variable type (string)
        :param value: variable value
        """
        self.type = var_type
        self.value = value

    def value_str(self):
        """
        String representation of variable value
        :return: value string repr
        """
        if type(self.value) is bool:
            if self.value:
                return 'true'
            else:
                return 'false'
        else:
            return str(self.value)

    def __repr__(self):
        """
        Variable representation
        :return: variable representation
        """
        if self.value is None:
            return 'uninitialized'
        else:
            return self.value_str() + ' (' + str(self.type) + ')'


class Program:
    """
    Class representing IPPcode18 program
    """
    def __init__(self, xml_dom: Element):
        """
        Initialize program
        :param xml_dom: valid XML DOM element
        """
        self._xml_dom = xml_dom
        self._inst_list = None
        self._frames = {
            Frame.GF: dict(),
            Frame.LF: list(),
            Frame.TF: None
        }
        self.labels = dict()
        self.call_stack = list()
        self._curr_inst = 0
        self._data_stack = list()

    def analyze(self):
        """
        Analyze program, finds all labels and checks label duplicity. Has to ba called before interpretation
        """
        self._inst_list = list()
        ordered_inst = sorted(self._xml_dom, key=lambda i: int(i.attrib['order']))
        for instruction in ordered_inst:
            self._inst_list.append(Instruction.from_xml_dom(instruction))
            if self._inst_list[-1].opcode == 'LABEL':
                self._add_label(self._inst_list[-1], len(self._inst_list) - 1)

    def _add_label(self, inst: Instruction, inst_addr: int):
        """
        Add label to list, raises exception if label already exists
        :param inst: instruction
        :param inst_addr: label address
        """
        label = inst.args[0].value
        if label in self.labels:
            raise SemanticError("Pokus o redefinici návěstí {}".format(label))
        self.labels[label] = inst_addr

    def interpret(self):
        """
        Interpret program, analyze method has to be called before this one
        """
        assert self._inst_list is not None
        self._curr_inst = 0
        while self._curr_inst < len(self._inst_list):
            self._inst_list[self._curr_inst].run(self)
            self._curr_inst += 1

    def jump_to_label(self, label: str):
        """
        Jump to given label
        :param label: label name
        """
        if label not in self.labels:
            raise SemanticError("Skok na neexistující návěstí {}".format(label))
        self._curr_inst = self.labels[label]

    def get_frame(self, frame: Frame):
        """
        Get frame variables from given frame type
        :param frame: frame type
        :return: list of variables
        """
        if frame == Frame.LF:
            if len(self._frames[frame]) == 0:
                raise UndefinedFrame("Pokus o přístup k prázdnému zásobníku lokálních rámců")
            return self._frames[frame][-1]
        if frame == Frame.TF and self._frames[frame] is None:
            raise UndefinedFrame("Přístup k nedefinovanému rámci {}".format(frame))
        return self._frames[frame]

    def get_var(self, frame: Frame, var: str):
        """
        Get variable from given frame
        :param frame: frame type
        :param var: variable name
        :return: Variable instance
        """
        frame_vars = self.get_frame(frame)
        if var not in frame_vars:
            raise UndefinedVar("Přístup k nedefinované proměnné '{}' na rámci {}".format(var, frame))
        return frame_vars[var]

    def create_tmp_frame(self):
        """
        Create temporary frame, overrides previous one
        """
        self._frames[Frame.TF] = dict()

    def push_tmp_frame(self):
        """
        Push temporary frame to stack of local frames, temporary frame becomes undefined
        """
        self._frames[Frame.LF].append(self.get_frame(Frame.TF))
        self._frames[Frame.TF] = None

    def pop_to_tmp_frame(self):
        """
        Pop local frame from stack to temporary frame, temporary frame is overridden
        """
        if len(self._frames[Frame.LF]) == 0:
            raise UndefinedFrame("Pokus o přístup k prázdnému zásobníku lokálních rámců")
        self._frames[Frame.TF] = self._frames[Frame.LF].pop()

    def create_var(self, frame: Frame, name: str):
        """
        Create uninitialized variable in given frame
        :param frame: frame type
        :param name: variable name
        """
        self.get_frame(frame)[name] = Variable()

    def call(self, label: str):
        """
        Call given label as function
        :param label: label name
        """
        if label not in self.labels:
            raise SemanticError("Volání nedefinované funkce {}".format(label))
        self.call_stack.append(self._curr_inst)
        self._curr_inst = self.labels[label]

    def ret(self):
        """
        Return from current function
        """
        if len(self.call_stack) == 0:
            raise MissingValue("Volání instrukce RETURN mimo funkci")
        self._curr_inst = self.call_stack.pop()

    def data_push(self, value):
        """
        Push data on data stack
        :param value: value to push
        """
        self._data_stack.append(value)

    def data_pop(self):
        """
        Pop data from data stack
        :return: value
        """
        if len(self._data_stack) == 0:
            raise MissingValue("Pokus o čtení z prázdného datového zásobníku")
        return self._data_stack.pop()

    def get_inst_number(self):
        """
        Get current instruction number
        :return: instruction number
        """
        return self._curr_inst + 1

    def get_current_inst(self):
        """
        Get current Instruction
        :return: Instruction instance
        """
        return self._inst_list[self._curr_inst]

    def print_debug(self):
        """
        Print current program state to stderr
        """
        print('Current instruction: {}'.format(self._curr_inst), file=sys.stderr)
        print('Global frame: {}'.format(repr(self.get_frame(Frame.GF))), file=sys.stderr)
        print('Temporary frame: {}'.format(repr(self.get_frame(Frame.TF))), file=sys.stderr)
        print('Local frame: {}'.format(repr(self.get_frame(Frame.LF))), file=sys.stderr)
