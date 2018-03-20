from xml.etree import ElementTree
from classes.python.exceptions import XMLFormatError


class IPPParser:
    """
    Parse XML IPPcode18 representation to internal representation
    """
    def parse_from_file(self, file: str):
        try:
            xml_dom = ElementTree.parse(file).getroot()
        except ElementTree.ParseError:
            raise XMLFormatError("Vstupní XML nemá správný formát'")
        return self._parse(xml_dom)

    def parse_from_string(self, xml_string: str):
        try:
            xml_dom = ElementTree.fromstring(xml_string)
        except ElementTree.ParseError:
            raise XMLFormatError("Vstupní XML nemá správný formát'")
        return self._parse(xml_dom)

    def _parse(self, xml_dom: ElementTree.Element):
        self._check_xml_structure(xml_dom)
        return False

    def _check_xml_structure(self, xml_dom: ElementTree.Element) -> None:
        self._check_root_elem(xml_dom)
        self._check_instructions(xml_dom)

    def _check_root_elem(self, root_elem: ElementTree.Element) -> None:
        if root_elem.tag != "program":
            raise XMLFormatError("Vstupní XML musí mít kořenový element <program>")

        has_language = False
        for attrib, value in root_elem.attrib.items():
            if attrib == 'language':
                if value != 'IPPcode18':
                    raise XMLFormatError("Kořenový element <program> musí obsahovat atribut 'language=\"IPPcode18\"'")
                else:
                    has_language = True
            elif attrib not in ['name', 'description']:
                raise XMLFormatError("Nepovolený atribut '{}' elementu '<program>".format(attrib))

        if not has_language:
            raise XMLFormatError("Kořenový element <program> musí obsahovat atribut 'language=\"IPPcode18\"'")

    def _check_instructions(self, root_elem: ElementTree.Element) -> None:
        for child in root_elem:
            pass

