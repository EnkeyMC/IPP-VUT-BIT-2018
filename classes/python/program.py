from xml.etree.ElementTree import Element

from classes.python.instruction import Instruction
from classes.python.exceptions import SemanticError


class Variable:
    def __init__(self):
        self.type = None
        self.value = None


class SymbolTables:
    def __init__(self):
        self.st_glob = dict()
        self.st_temp = dict()
        self.st_local = list()

    def __getitem__(self, item: str) -> dict:
        if item == 'TF':
            return self.st_temp
        elif item == 'LF':
            return self.st_local[-1]
        else:
            return self.st_glob


class Program:
    def __init__(self, xml_dom: Element):
        self._xml_dom = xml_dom
        self._inst_list = None
        self.symbol_tables = SymbolTables()
        self.labels = dict()
        self.call_stack = list()
        self._curr_inst = 0

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

    def jump_to_label(self, label: str):
        assert label in self.labels
        self._curr_inst = self.labels[label]

    def next_inst(self):
        self._curr_inst += 1
