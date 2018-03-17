from xml.etree import ElementTree


class IPPParser:
    """
    Parse XML IPPcode18 representation to internal representation
    """
    def parse_from_file(self, file: str):
        return self._parse(ElementTree.parse(file))

    def parse_from_string(self, xml_string: str):
        return self._parse(ElementTree.fromstring(xml_string))

    def _parse(self, xml_dom: ElementTree.Element):
        return xml_dom
