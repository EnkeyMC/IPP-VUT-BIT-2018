from xml.etree.ElementTree import Element

import sys

from classes.python.instruction import Instruction
from classes.python.exceptions import SemanticError
from classes.python.frame import Frame


class Variable:
    def __init__(self, var_type=None, value=None):
        self.type = var_type
        self.value = value

    def value_str(self):
        if type(self.value) is bool:
            if self.value:
                return 'true'
            else:
                return 'false'
        else:
            return str(self.value)

    def __repr__(self):
        if self.value is None:
            return 'uninitialized'
        else:
            return self.value_str() + ' (' + str(self.type) + ')'


class Program:
    def __init__(self, xml_dom: Element):
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
        self._inst_list = list()
        ordered_inst = sorted(self._xml_dom, key=lambda i: int(i.attrib['order']))
        for instruction in ordered_inst:
            self._inst_list.append(Instruction.from_xml_dom(instruction))
            if self._inst_list[-1].opcode == 'LABEL':
                self._add_label(self._inst_list[-1], len(self._inst_list) - 1)

    def _add_label(self, inst: Instruction, inst_addr: int):
        label = inst.args[0].value
        if label in self.labels:
            raise SemanticError("Pokus o redefinici návěstí {}".format(label))
        self.labels[label] = inst_addr

    def interpret(self):
        assert self._inst_list is not None
        self._curr_inst = 0
        while self._curr_inst < len(self._inst_list):
            self._inst_list[self._curr_inst].run(self)
            self.next_inst()

    def jump_to_label(self, label: str):
        assert label in self.labels
        self._curr_inst = self.labels[label]

    def next_inst(self):
        self._curr_inst += 1

    def get_frame(self, frame: Frame):
        if frame == Frame.LF:
            return self._frames[frame][-1]
        return self._frames[frame]

    def get_var(self, frame: Frame, var: str):
        return self.get_frame(frame)[var]

    def create_tmp_frame(self):
        self._frames[Frame.TF] = dict()

    def push_tmp_frame(self):
        self._frames[Frame.LF].append(self._frames[Frame.TF])
        self._frames[Frame.TF] = None

    def pop_to_tmp_frame(self):
        self._frames[Frame.TF] = self._frames[Frame.LF].pop()

    def create_var(self, frame: Frame, name: str):
        self.get_frame(frame)[name] = Variable()

    def call(self, label: str):
        self.call_stack.append(self._curr_inst)
        self._curr_inst = self.labels[label]

    def ret(self):
        self._curr_inst = self.call_stack.pop()

    def data_push(self, value):
        self._data_stack.append(value)

    def data_pop(self):
        return self._data_stack.pop()

    def get_inst_number(self):
        return self._curr_inst + 1

    def get_current_inst(self):
        return self._inst_list[self._curr_inst]

    def print_debug(self):
        print('Current instruction: {}'.format(self._curr_inst), file=sys.stderr)
        print('Global frame: {}'.format(repr(self.get_frame(Frame.GF))), file=sys.stderr)
        print('Temporary frame: {}'.format(repr(self.get_frame(Frame.TF))), file=sys.stderr)
        print('Local frame: {}'.format(repr(self.get_frame(Frame.LF))), file=sys.stderr)
