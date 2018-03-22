from xml.etree.ElementTree import Element
from classes.python.frame import Frame


class Arg:
    def __init__(self, arg_type: str, value: str):
        self.type = arg_type
        if arg_type == 'var':
            parts = value.split('@')
            self.frame = Frame.str_to_frame(parts[0])
            self.value = parts[1]
        else:
            self.value = value


class Instruction:

    def __init__(self, opcode: str, args: list):
        self.opcode = opcode
        self.args = args
        self.func = None

    def run(self, context):
        self.func(context, *self.args)

    @staticmethod
    def from_xml_dom(inst_dom: Element):
        opcode = inst_dom.attrib['opcode']
        args = list()

        for arg in inst_dom:
            args.append(Arg(arg.attrib['type'], arg.text))

        return Instruction(opcode, args)
