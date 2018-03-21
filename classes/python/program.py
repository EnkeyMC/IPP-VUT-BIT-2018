from xml.etree.ElementTree import Element
from enum import Enum

from classes.python.instruction import Instruction
from classes.python.exceptions import SemanticError


class Frame(Enum):
    TF = 0
    LF = 1
    GF = 2

    @staticmethod
    def str_to_frame(frame_str: str):
        if frame_str == 'TF':
            return Frame.TF
        if frame_str == 'LF':
            return Frame.LF
        else:
            return Frame.GF


class Program:

    def __init__(self, xml_dom: Element):
        self.xml_dom = xml_dom
        self.inst_list = None
        self.labels = dict()

    def analyze(self):
        self.inst_list = list()
        ordered_inst = sorted(self.xml_dom, key=lambda inst: int(inst.attrib['order']))
        for inst in ordered_inst:
            self.inst_list.append(Instruction.from_xml_dom(inst))
            if self.inst_list[-1].opcode == 'LABEL':
                label = self.inst_list[-1].args[0].value
                if label in self.labels:
                    raise SemanticError("Pokus o redefinici návěstí {}".format(label))
                self.labels[label] = len(self.inst_list)

    def interpret(self):
        pass
