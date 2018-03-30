from unittest.case import TestCase

from classes.python.ipp_parser import IPPParser
from classes.python.exceptions import XMLFormatError, SrcSyntaxError, LexicalError, SemanticError


class TestIPPParser(TestCase):
    def setUp(self):
        self.parser = IPPParser()

    def test_empty(self):
        self.assertIsNotNone(self.parser.parse_from_string(
            """<?xml version="1.0" encoding="UTF-8"?><program language="IPPcode18"></program>"""))

    def test_no_root_tag(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>"""
        )

    def test_no_lang_attrib(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?><program></program>"""
        )

    def test_name_attrib(self):
        try:
            self.parser.parse_from_string(
                """<?xml version="1.0" encoding="UTF-8" ?><program language="IPPcode18" name="test"></program>"""
            )
        except Exception as e:
            self.fail("Exception {} was thrown!".format(repr(e)))

    def test_description_attrib(self):
        try:
            self.parser.parse_from_string(
                """<?xml version="1.0" encoding="UTF-8" ?><program language="IPPcode18" description="test"></program>"""
            )
        except Exception as e:
            self.fail("Exception {} was thrown!".format(repr(e)))

    def test_invalid_program_attrib(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?><program language="IPPcode18" invalid="test"></program>"""
        )

    def test_invalid_inst_tag(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <invalid opcode="CREATEFRAME">
                </invalid>
            </program>"""
        )

    def test_order_nan(self):
        self.assertRaises(
            LexicalError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="f" opcode="CREATEFRAME">
                </instruction>
            </program>"""
        )

    def test_order_empty(self):
        self.assertRaises(
            LexicalError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="" opcode="CREATEFRAME">
                </instruction>
            </program>"""
        )

    def test_instruction_invalid_attr(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="CREATEFRAME" invalid="dfa">
                </instruction>
            </program>"""

        )

    def test_invalid_opcode(self):
        self.assertRaises(
            SrcSyntaxError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="INVALID">
                </instruction>
            </program>"""

        )

    def test_invalid_inst_arg(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="CREATEFRAME">
                    <invalid></invalid>
                </instruction>
            </program>"""
        )

    def test_multiple_opcodes(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="CREATEFRAME" opcode="POPFRAME">
                </instruction>
            </program>"""
        )

    def test_missing_opcode(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1">
                </instruction>
            </program>"""
        )

    def test_missing_order(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction opcode="CREATEFRAME">
                </instruction>
            </program>"""
        )

    def test_inst_skipped_arg(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="NOT">
                    <arg2 type="var">GF@var</arg2>
                    <arg3 type="var">GF@var</arg3>
                </instruction>
            </program>"""
        )

    def test_duplicate_arg(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="NOT">
                    <arg1 type="var">GF@var</arg1>
                    <arg1 type="var">GF@var</arg1>
                </instruction>
            </program>"""
        )

    def test_invalid_number_of_args(self):
        self.assertRaises(
            SrcSyntaxError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="NOT">
                    <arg1 type="var">GF@var</arg1>
                </instruction>
            </program>"""
        )

    def test_invalid_arg_type(self):
        self.assertRaises(
            SrcSyntaxError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="NOT">
                    <arg1 type="var">GF@var</arg1>
                    <arg2 type="invalid">GF@var</arg2>
                </instruction>
            </program>"""
        )

    def test_invalid_arg_attrib(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="NOT">
                    <arg1 type="var">GF@var</arg1>
                    <arg2 type="var" wat="fd">GF@var</arg2>
                </instruction>
            </program>"""
        )

    def test_invalid_arg_not_type(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="NOT">
                    <arg1>GF@var</arg1>
                    <arg2 type="var">GF@var</arg2>
                </instruction>
            </program>"""
        )

    def test_int_invalid_chars(self):
        self.assertRaises(
            LexicalError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="MOVE">
                    <arg1 type="var">GF@var</arg1>
                    <arg2 type="int">nan</arg2>
                </instruction>
            </program>"""
        )

    def test_int_invalid_two_minuses(self):
        self.assertRaises(
            LexicalError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="MOVE">
                    <arg1 type="var">GF@var</arg1>
                    <arg2 type="int">--887</arg2>
                </instruction>
            </program>"""
        )

    def test_int_invalid_two_pluses(self):
        self.assertRaises(
            LexicalError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="MOVE">
                    <arg1 type="var">GF@var</arg1>
                    <arg2 type="int">++94</arg2>
                </instruction>
            </program>"""
        )

    def test_int_invalid_num_char_num(self):
        self.assertRaises(
            LexicalError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="MOVE">
                    <arg1 type="var">GF@var</arg1>
                    <arg2 type="int">84e5</arg2>
                </instruction>
            </program>"""
        )

    def test_int_valid(self):
        try:
            self.parser.parse_from_string(
                """<?xml version="1.0" encoding="UTF-8" ?>
                <program language="IPPcode18">
                    <instruction order="1" opcode="MOVE">
                        <arg1 type="var">GF@var</arg1>
                        <arg2 type="int">69</arg2>
                    </instruction>
                </program>"""
            )
        except Exception as e:
            self.fail(e)

    def test_bool_invalid(self):
        self.assertRaises(
            LexicalError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="MOVE">
                    <arg1 type="var">GF@var</arg1>
                    <arg2 type="bool">69</arg2>
                </instruction>
            </program>"""
        )

    def test_bool_true(self):
        try:
            self.parser.parse_from_string(
                """<?xml version="1.0" encoding="UTF-8" ?>
                <program language="IPPcode18">
                    <instruction order="1" opcode="MOVE">
                        <arg1 type="var">GF@var</arg1>
                        <arg2 type="bool">true</arg2>
                    </instruction>
                </program>"""
            )
        except Exception as e:
            self.fail(e)

    def test_bool_false(self):
        try:
            self.parser.parse_from_string(
                """<?xml version="1.0" encoding="UTF-8" ?>
                <program language="IPPcode18">
                    <instruction order="1" opcode="MOVE">
                        <arg1 type="var">GF@var</arg1>
                        <arg2 type="bool">false</arg2>
                    </instruction>
                </program>"""
            )
        except Exception as e:
            self.fail(e)

    def test_string_simple(self):
        try:
            self.parser.parse_from_string(
                """<?xml version="1.0" encoding="UTF-8" ?>
                <program language="IPPcode18">
                    <instruction order="1" opcode="MOVE">
                        <arg1 type="var">GF@var</arg1>
                        <arg2 type="string">falsey</arg2>
                    </instruction>
                </program>"""
            )
        except Exception as e:
            self.fail(e)

    def test_string_empty(self):
        try:
            self.parser.parse_from_string(
                """<?xml version="1.0" encoding="UTF-8" ?>
                <program language="IPPcode18">
                    <instruction order="1" opcode="MOVE">
                        <arg1 type="var">GF@var</arg1>
                        <arg2 type="string" />
                    </instruction>
                </program>"""
            )
        except Exception as e:
            self.fail(e)

    def test_string_esc_seq(self):
        try:
            self.parser.parse_from_string(
                """<?xml version="1.0" encoding="UTF-8" ?>
                <program language="IPPcode18">
                    <instruction order="1" opcode="MOVE">
                        <arg1 type="var">GF@var</arg1>
                        <arg2 type="string">\\032032</arg2>
                    </instruction>
                </program>"""
            )
        except Exception as e:
            self.fail(e)

    def test_string_gt(self):
        try:
            self.parser.parse_from_string(
                """<?xml version="1.0" encoding="UTF-8" ?>
                <program language="IPPcode18">
                    <instruction order="1" opcode="MOVE">
                        <arg1 type="var">GF@var</arg1>
                        <arg2 type="string">&gt;</arg2>
                    </instruction>
                </program>"""
            )
        except Exception as e:
            self.fail(e)

    def test_string_invalid_whitespace(self):
        self.assertRaises(
            LexicalError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="MOVE">
                    <arg1 type="var">GF@var</arg1>
                    <arg2 type="string">Ahoj Světe</arg2>
                </instruction>
            </program>"""
        )

    def test_string_invalid_hash(self):
        self.assertRaises(
            LexicalError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="MOVE">
                    <arg1 type="var">GF@var</arg1>
                    <arg2 type="string">WJDO#dfsa</arg2>
                </instruction>
            </program>"""
        )

    def test_string_invalid_esc_seq(self):
        self.assertRaises(
            LexicalError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="MOVE">
                    <arg1 type="var">GF@var</arg1>
                    <arg2 type="string">\\4A5</arg2>
                </instruction>
            </program>"""
        )

    def test_string_invalid_esc_seq_2(self):
        self.assertRaises(
            LexicalError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="MOVE">
                    <arg1 type="var">GF@var</arg1>
                    <arg2 type="string">\\01</arg2>
                </instruction>
            </program>"""
        )

    def test_var_valid_spec_chars(self):
        try:
            self.parser.parse_from_string(
                """<?xml version="1.0" encoding="UTF-8" ?>
                <program language="IPPcode18">
                    <instruction order="1" opcode="MOVE">
                        <arg1 type="var">GF@_var-&amp;*$%</arg1>
                        <arg2 type="int">5</arg2>
                    </instruction>
                </program>"""
            )
        except Exception as e:
            self.fail(e)

    def test_var_invalid_char_hash(self):
        self.assertRaises(
            LexicalError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="MOVE">
                    <arg1 type="var">GF@var#84</arg1>
                    <arg2 type="string">saf</arg2>
                </instruction>
            </program>"""
        )

    def test_var_invalid_char_space(self):
        self.assertRaises(
            LexicalError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="MOVE">
                    <arg1 type="var">GF@var iable</arg1>
                    <arg2 type="string">knm</arg2>
                </instruction>
            </program>"""
        )

    def test_label_valid_spec_chars(self):
        try:
            self.parser.parse_from_string(
                """<?xml version="1.0" encoding="UTF-8" ?>
                <program language="IPPcode18">
                    <instruction order="1" opcode="LABEL">
                        <arg1 type="label">_label-&amp;*$%</arg1>
                    </instruction>
                </program>"""
            )
        except Exception as e:
            self.fail(e)

    def test_label_invalid_char_hash(self):
        self.assertRaises(
            LexicalError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="LABEL">
                    <arg1 type="label">label#84</arg1>
                </instruction>
            </program>"""
        )

    def test_label_invalid_char_space(self):
        self.assertRaises(
            LexicalError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="LABEL">
                    <arg1 type="label">label 4</arg1>
                </instruction>
            </program>"""
        )

    def test_type_valid_int(self):
        try:
            self.parser.parse_from_string(
                """<?xml version="1.0" encoding="UTF-8" ?>
                <program language="IPPcode18">
                    <instruction order="1" opcode="READ">
                        <arg1 type="var">GF@var</arg1>
                        <arg2 type="type">int</arg2>
                    </instruction>
                </program>"""
            )
        except Exception as e:
            self.fail(e)

    def test_type_valid_string(self):
        try:
            self.parser.parse_from_string(
                """<?xml version="1.0" encoding="UTF-8" ?>
                <program language="IPPcode18">
                    <instruction order="1" opcode="READ">
                        <arg1 type="var">GF@var</arg1>
                        <arg2 type="type">string</arg2>
                    </instruction>
                </program>"""
            )
        except Exception as e:
            self.fail(e)

    def test_type_valid_bool(self):
        try:
            self.parser.parse_from_string(
                """<?xml version="1.0" encoding="UTF-8" ?>
                <program language="IPPcode18">
                    <instruction order="1" opcode="READ">
                        <arg1 type="var">GF@var</arg1>
                        <arg2 type="type">bool</arg2>
                    </instruction>
                </program>"""
            )
        except Exception as e:
            self.fail(e)

    def test_type_invalid(self):
        self.assertRaises(
            LexicalError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                    <instruction order="1" opcode="READ">
                        <arg1 type="var">GF@var</arg1>
                        <arg2 type="type">type</arg2>
                    </instruction>
            </program>"""
        )
