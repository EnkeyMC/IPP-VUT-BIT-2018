from xml.etree.ElementTree import Element

from classes.python.instruction import Instruction
from classes.python.exceptions import SemanticError


class Program:
    def __init__(self, xml_dom: Element):
        self.xml_dom = xml_dom
        self.inst_list = None
        self.labels = dict()

    def analyze(self):
        self.inst_list = list()
        ordered_inst = sorted(self.xml_dom, key=lambda i: int(i.attrib['order']))
        for instruction in ordered_inst:
            self.inst_list.append(Instruction.from_xml_dom(instruction))
            if self.inst_list[-1].opcode == 'LABEL':
                label = self.inst_list[-1].args[0].value
                if label in self.labels:
                    raise SemanticError("Pokus o redefinici návěstí {}".format(label))
                self.labels[label] = len(self.inst_list)

    def interpret(self):
        pass
